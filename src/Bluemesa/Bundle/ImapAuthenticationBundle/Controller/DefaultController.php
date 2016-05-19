<?php
/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluemesa\Bundle\ImapAuthenticationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Security;

class DefaultController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAction()
    {
        $error = $this->getAuthenticationError();

        return $this->render('BluemesaImapAuthenticationBundle:Default:login.html.twig', array(
            'last_username' => $this->get('request')->getSession()->get(Security::LAST_USERNAME),
            'error'         => $error,
            'token'         => $this->generateToken(),
        ));
    }

    /**
     * @return string
     */
    protected function getAuthenticationError()
    {
        if ($this->get('request')->attributes->has(Security::AUTHENTICATION_ERROR)) {
            return $this->get('request')->attributes->get(Security::AUTHENTICATION_ERROR);
        }

        return $this->get('request')->getSession()->get(Security::AUTHENTICATION_ERROR);
    }

    /**
     * @return string
     */
    protected function generateToken()
    {
        $token = $this->get('security.csrf.token_manager')
                      ->getToken('imap-authenticate');

        return $token;
    }
}
