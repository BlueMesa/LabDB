<?php

namespace Bluemesa\Bundle\ImapAuthenticationBundle\Provider;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\ChainUserProvider;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use JMS\DiExtraBundle\Annotation as DI;

use Bluemesa\Bundle\ImapAuthenticationBundle\Exception\ConnectionException;
use Bluemesa\Bundle\ImapAuthenticationBundle\Event\ImapUserEvent;
use Bluemesa\Bundle\ImapAuthenticationBundle\Event\ImapEvents;
use Bluemesa\Bundle\ImapAuthenticationBundle\Manager\ImapUserManagerInterface;
use Bluemesa\Bundle\ImapAuthenticationBundle\User\ImapUserInterface;

use Egulias\EmailValidator\EmailValidator;


/**
 * IMAP Authentication Provider
 *
 * @DI\Service("bluemesa_imap.security.authentication.provider")
 * 
 * 
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class ImapAuthenticationProvider implements AuthenticationProviderInterface
{
    private $userProvider;
    private $userChecker;
    private $imapManager;
    private $dispatcher;
    private $providerKey;
    private $hideUserNotFoundExceptions;
    private $emailValidator;
    
    /**
     * Constructor
     *
     * Please note that $hideUserNotFoundExceptions is true by default in order
     * to prevent a possible brute-force attack.
     *
     * @DI\InjectParams({
     *      "userProvider" = @DI\Inject("user_provider"),
     *      "userChecker" = @DI\Inject("security.user_checker"),
     *      "imapManager" = @DI\Inject("bluemesa_imap.imap_manager"),
     *      "dispatcher" = @DI\Inject("event_dispatcher", required = false),
     *      "providerKey" = @DI\Inject("%%"),
     *      "hideUserNotFoundExceptions" = @DI\Inject("%security.authentication.hide_user_not_found%")
     * })
     * 
     * @param UserProviderInterface    $userProvider
     * @param UserCheckerInterface     $userChecker
     * @param ImapUserManagerInterface $imapManager
     * @param EventDispatcherInterface $dispatcher
     * @param string                   $providerKey
     * @param Boolean                  $hideUserNotFoundExceptions
     */
    public function __construct(
            UserProviderInterface $userProvider,
            UserCheckerInterface $userChecker,
            ImapUserManagerInterface $imapManager,
            EventDispatcherInterface $dispatcher = null,
            $providerKey = 'bluemesa-imap',
            $hideUserNotFoundExceptions = true )
    {
        $this->userProvider = $userProvider;
        $this->userChecker = $userChecker;
        $this->imapManager = $imapManager;
        $this->dispatcher = $dispatcher;
        $this->providerKey = $providerKey;
        $this->hideUserNotFoundExceptions = $hideUserNotFoundExceptions;
        $this->emailValidator = new EmailValidator();
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token)
    {
        if (! $this->supports($token)) {
            throw new AuthenticationException('Unsupported token');
        }
        
        try {
            $user = $this->retrieveUser($token);
            $authenticatedToken = $this->imapAuthenticate($user, $token);
            if ($user instanceof UserInterface) {
                $this->userChecker->checkPostAuth($user);
            }
            
            return $authenticatedToken;

        } catch (\Exception $exception) {
            if (($exception instanceof ConnectionException ||
                    $exception instanceof UsernameNotFoundException)) {
                $this->throwBadCredentialsException($exception);
            }
            throw $exception;
        }
    }

    /**
     * Retrieve user from security token
     * 
     * @param  TokenInterface $token
     * @return ImapUserInterface
     * @throws UsernameNotFoundException
     * @throws AuthenticationServiceException
     */
    public function retrieveUser($token)
    {
        try {
            $user = $this->userProvider->loadUserByUsername($token->getUsername());
            if (!$user instanceof ImapUserInterface) {
                throw new AuthenticationServiceException(
                        'The user provider must return a ImapUserInterface object.');
            }
        } catch (UsernameNotFoundException $notFound) {
            $user = null;
            if ($this->userProvider instanceof ImapUserProviderInterface) {
                $user = $this->userProvider->createUser($token);
                if ($user === null) {
                    $user = $token->getUsername();
                }
            } elseif ($this->userProvider instanceof ChainUserProvider) {
                foreach ($this->userProvider->getProviders() as $provider) {
                    if ($provider instanceof ImapUserProviderInterface) {
                        try {
                            $user = (null === $user) ? $provider->createUser($token) : $user;
                        } catch (UsernameNotFoundException $e) {}
                    }
                }
                
            }
            if ($user === null) {
                throw $notFound;
            }
        }

        return $user;
    }
    
    /**
     * Authentication logic for IMAP user
     *
     * @param  ImapUserInterface      $user
     * @param  TokenInterface         $token
     * @return UsernamePasswordToken  $token
     */
    private function imapAuthenticate(ImapUserInterface $user, TokenInterface $token)
    {
        $userEvent = new ImapUserEvent($user);
        
        if (null !== $this->dispatcher) {
            try {
                $this->dispatcher->dispatch(ImapEvents::PRE_BIND, $userEvent);
            } catch (AuthenticationException $exception) {
                $this->throwBadCredentialsException($exception);
            }
        }
        
        $this->bind($user, $token);
        
        if (null === $user->getUsername()) {
            $user = $this->reloadUser($user);
        }
        
        if (null !== $this->dispatcher) {
            $userEvent = new ImapUserEvent($user);
            try {
                $this->dispatcher->dispatch(ImapEvents::POST_BIND, $userEvent);
            } catch (AuthenticationException $exception) {
                $this->throwBadCredentialsException($exception);
            }
        }
        
        $authenticatedToken = new UsernamePasswordToken($userEvent->getUser(), 
                null, $this->providerKey, $userEvent->getUser()->getRoles());
        $authenticatedToken->setAttributes($token->getAttributes());
        
        return $authenticatedToken;
    }

    /**
     * Authenticate the user with IMAP login.
     *
     * @param ImapUserInterface $user
     * @param TokenInterface    $token
     *
     * @return true
     */
    private function bind(ImapUserInterface $user, TokenInterface $token)
    {
        $this->imapManager
            ->setUsername($user->getUsername())
            ->setPassword($token->getCredentials());
        $this->imapManager->auth();

        return true;
    }

    /**
     * Reload user with the username
     *
     * @param  ImapUserInterface $user
     * @return ImapUserInterface $user
     */
    private function reloadUser(ImapUserInterface $user)
    {
        try {
            $user = $this->userProvider->refreshUser($user);
        } catch (UsernameNotFoundException $exception) {
            $this->throwBadCredentialsException($exception);
        }

        return $user;
    }

    /**
     * Check whether this provider supports the given token.
     *
     * @param  TokenInterface $token
     * @return boolean
     */
    public function supports(TokenInterface $token)
    {
        return (($token instanceof UsernamePasswordToken) &&
                ($token->getProviderKey() === $this->providerKey) &&
                ($this->emailValidator->isValid($token->getUsername()))&&
                ($this->imapManager->supports($token->getUsername())));
    }
    
    private function throwBadCredentialsException($exception)
    {
        if ($this->hideUserNotFoundExceptions) {
            throw new BadCredentialsException('Bad credentials', 0, $exception);
        }
        throw $exception;
    }
}
