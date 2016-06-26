<?php

/*
 * Copyright 2013 Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Bluemesa\IcmImapUserBundle\Security;

use JMS\DiExtraBundle\Annotation as DI;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

use FOS\UserBundle\Security\UserProvider as BaseUserProvider;
use FOS\UserBundle\Model\UserManagerInterface;

use Egulias\EmailValidator\EmailParser;
use Egulias\EmailValidator\EmailLexer;

use Bluemesa\Bundle\ImapAuthenticationBundle\Provider\ImapUserProviderInterface;
use Bluemesa\Bundle\UserBundle\Entity\User;

/**
 * ICM IMAP UserProvider
 *
 * @DI\Service("bluemesa.user_provider.icm_imap")
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
     * Create a new user using IMAP data source
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
     * @param  TokenInterface $token
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
     * @param UserInterface         $user
     */
    private function setUserData(UserInterface $user)
    {
        $email = $user->getUsername();
        $parts = $this->emailParser->parse($email);
        $this->verifyDomain($parts['domain']);
        
        $userNameArray = explode('.', $parts['local']);
        
        if ($user instanceof User) {
            $givenName = ucfirst($userNameArray[0]);
            $user->setGivenName($givenName);
            $lastName = ucfirst($userNameArray[1]);
            $user->setSurname($lastName);
        }
        
        $user->setEmail($email);
        $user->setPlainPassword($this->generateRandomString());
        $user->addRole('ROLE_USER');
        $user->addRole('ROLE_ICM');
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
        if ($domain != 'icm-institute.org') {
            throw new UsernameNotFoundException();
        }
    }
}
