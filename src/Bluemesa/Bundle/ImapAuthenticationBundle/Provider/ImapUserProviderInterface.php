<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Bluemesa\Bundle\ImapAuthenticationBundle\Provider;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Description of ImapUserProviderInterface
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
interface ImapUserProviderInterface {
    
    /**
     * @param TokenInterface $token
     */
    function createUser(TokenInterface $token);

    /**
     * @param TokenInterface $token
     */
    function updateUser(TokenInterface $token);
}
