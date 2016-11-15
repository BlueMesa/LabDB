<?php

/*
 * This file is part of the IcmImapUserBundle.
 *
 * Copyright (c) 2016 BlueMesa LabDB Contributors <labdb@bluemesa.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bluemesa\IcmImapUserBundle\Security;

use Bluemesa\Bundle\ImapAuthenticationBundle\Provider\ImapUserProvider as BaseUserProvider;
use Bluemesa\Bundle\UserBundle\Entity\User;
use FOS\UserBundle\Model\UserInterface;
use JMS\DiExtraBundle\Annotation as DI;


/**
 * ICM IMAP UserProvider
 *
 * @DI\Service("bluemesa.user_provider.icm_imap")
 * 
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class ImapUserProvider extends BaseUserProvider
{
    const DOMAIN = 'icm-institute.org';

    /**
     * {@inheritdoc}
     */
    protected function setUserData(UserInterface $user)
    {
        $email = $user->getUsername();
        $parts = $this->splitUsername($email);
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
}
