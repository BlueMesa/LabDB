<?php

/*
 * This file is part of the BluemesaGmailImapUserBundle.
 * 
 * Copyright (c) 2016 BlueMesa LabDB Contributors <labdb@bluemesa.eu>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bluemesa\GmailImapUserBundle\Security;

use FOS\UserBundle\Model\UserInterface;
use JMS\DiExtraBundle\Annotation as DI;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

use FOS\UserBundle\Security\UserProvider as BaseUserProvider;
use FOS\UserBundle\Model\UserManagerInterface;

use Egulias\EmailValidator\EmailParser;
use Egulias\EmailValidator\EmailLexer;

use Bluemesa\Bundle\ImapAuthenticationBundle\Provider\ImapUserProviderInterface;
use Bluemesa\Bundle\UserBundle\Entity\User;

/**
 * Gmail IMAP UserProvider
 *
 * @DI\Service("bluemesa.user_provider.gmail_imap")
 * 
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class ImapUserProvider extends BaseUserProvider implements ImapUserProviderInterface
{
    private $emailParser;
    
    /**
     * @DI\InjectParams({
     *     "userManager" = @DI\Inject("fos_user.user_manager")
     * })
     * 
     * {@inheritDoc}
     */
    public function __construct(UserManagerInterface $userManager)
    {
        parent::__construct($userManager);
        $this->emailParser = new EmailParser(new EmailLexer());
    }

    /**
     * {@inheritDoc}
     */
    public function loadUserByUsername($username)
    {
        $parts = $this->emailParser->parse($username);
        $this->verifyDomain($parts['domain']);        
        $user = parent::loadUserByUsername($username);
        
        return $user;
    }
    
    /**
     * Create a new user using imap data source
     *
     * @param  TokenInterface $token
     * @return UserInterface
     */
    public function createUser(TokenInterface $token)
    {
        $user = $this->userManager->createUser();
        $user->setUsername($token->getUsername());
        $this->setUserData($user);

        return $user;
    }

    /**
     * Update user using imap data source
     *
     * @param  TokenInterface  $token
     * @return UserInterface
     */
    public function updateUser(TokenInterface $token)
    {
        $user = $this->loadUserByUsername($token->getUsername());
        $this->setUserData($user);

        return $user;
    }

    /**
     * Set user data using imap data source
     *
     * @param UserInterface  $user
     */
    private function setUserData(UserInterface $user)
    {
        $email = $user->getUsername();
        $parts = $this->emailParser->parse($email);
        $this->verifyDomain($parts['domain']);
        
        $user->setEmail($email);
        $user->setPlainPassword($this->generateRandomString());
        $user->addRole('ROLE_USER');
        $user->addRole('ROLE_GMAIL');
        $user->setEnabled(true);
        
        $this->userManager->updateUser($user);
    }

    /**
     * @param  int     $length
     * @return string
     */
    private function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    /**
     * @param  string $domain
     * @throws UsernameNotFoundException
     */
    private function verifyDomain($domain)
    {
        if ($domain != 'gmail.com') {
            throw new UsernameNotFoundException();
        }
    }
}
