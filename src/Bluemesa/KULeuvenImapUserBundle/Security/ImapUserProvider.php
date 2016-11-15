<?php

/*
 * This file is part of the KULeuvenImapUserBundle.
 *
 * Copyright (c) 2016 BlueMesa LabDB Contributors <labdb@bluemesa.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bluemesa\KULeuvenImapUserBundle\Security;

use Bluemesa\Bundle\ImapAuthenticationBundle\Provider\ImapUserProvider as BaseUserProvider;
use Bluemesa\Bundle\UserBundle\Entity\User;
use FOS\UserBundle\Model\UserInterface;
use JMS\DiExtraBundle\Annotation as DI;


/**
 * KU Leuven IMAP UserProvider
 *
 * @DI\Service("bluemesa.user_provider.kuleuven_imap")
 * 
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class ImapUserProvider extends BaseUserProvider
{
    const DOMAIN = 'kuleuven.be';

    /**
     * {@inheritdoc}
     */
    protected function setUserData(UserInterface $user)
    {
        $parts = $this->splitUsername($user->getUsername());
        $this->verifyDomain($parts['domain']);

        $localPart = $parts['local'];
        $unumber = str_replace('u', '', $localPart);

        $url = "http://www.kuleuven.be/wieiswie/en/person/" . $unumber;
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $html = curl_exec($ch);
        curl_close($ch);

        $dom = new \DOMDocument();
        @$dom->loadHTML($html);

        $uname = "";
        foreach ($dom->getElementsByTagName('h1') as $h1) {
            $uname = $h1->nodeValue;
        }

        $uemail = "";
        foreach ($dom->getElementsByTagName('script') as $script) {
            $emailPre = trim($script->nodeValue);
            if (!empty($emailPre)) {
                $emailPre = str_replace('document.write(String.fromCharCode(', '', $emailPre);
                $emailPre = str_replace('))', '', $emailPre);
                $emailArray = explode(',', $emailPre);

                $emailLink = "";
                foreach ($emailArray as $element) {
                    $emailLink .= chr(eval('return ' . $element . ';'));
                }

                $domInner = new \DOMDocument();
                @$domInner->loadHTML($emailLink);

                foreach ($domInner->getElementsByTagName('a') as $a) {
                    $uemail = $a->nodeValue;
                }
            }
        }

        $names = explode(" ", $uname);
        $mailparts = $this->splitUsername($uemail);
        $mailuname = $mailparts['local'];
        $mailnames = explode(".", $mailuname);

        $surnameIndex = 0;
        foreach ($names as $index => $name) {
            if (strtolower(substr($mailnames[1], 0, strlen($name))) === strtolower($name)) {
                $surnameIndex = $index;
            }
        }

        if ($user instanceof User) {
            $givenName = implode(" ", array_slice($names, 0, $surnameIndex));
            $user->setGivenName($givenName);
            $lastName = implode(" ", array_slice($names, $surnameIndex));
            $user->setSurname($lastName);
        }

        $user->setEmail($uemail);
        $user->setPlainPassword($this->generateRandomString());
        $user->addRole('ROLE_USER');
        $user->addRole('ROLE_KULEUVEN');
        $user->setEnabled(true);

        $this->userManager->updateUser($user);
    }
}
