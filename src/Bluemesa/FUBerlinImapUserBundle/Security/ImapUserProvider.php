<?php

/*
 * This file is part of the BluemesaGmailImapUserBundle.
 * 
 * Copyright (c) 2016 BlueMesa LabDB Contributors <labdb@bluemesa.eu>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bluemesa\FUBerlinImapUserBundle\Security;


use Bluemesa\Bundle\ImapAuthenticationBundle\Provider\ImapUserProvider as BaseUserProvider;
use FOS\UserBundle\Model\UserInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * FU-Berlin IMAP UserProvider
 *
 * @DI\Service("bluemesa.user_provider.fuberlin_imap")
 * 
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class ImapUserProvider extends BaseUserProvider
{
    const DOMAIN = 'zedat.fu-berlin.de';

    /**
     * {@inheritdoc}
     */
    protected function setUserData(UserInterface $user)
    {
        $email = $user->getUsername();
        $parts = $this->splitUsername($email);
        $this->verifyDomain($parts['domain']);
        
        $user->setEmail($email);
        $user->setPlainPassword($this->generateRandomString());
        $user->addRole('ROLE_USER');
        $user->addRole('ROLE_FUBERLIN');
        $user->setEnabled(true);
        
        $this->userManager->updateUser($user);
    }
}
