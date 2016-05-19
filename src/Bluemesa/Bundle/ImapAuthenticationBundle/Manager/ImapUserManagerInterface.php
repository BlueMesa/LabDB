<?php

namespace Bluemesa\Bundle\ImapAuthenticationBundle\Manager;

interface ImapUserManagerInterface
{
    /**
     * ImapUserManagerInterface constructor.
     * @param ImapConnectionInterface $conn
     */
    function __construct(ImapConnectionInterface $conn);

    /**
     * @return true
     * @throws ConnectionException
     */
    function auth();

    /**
     * @return string
     */
    function getUsername();

    /**
     * @return array
     */
    function getRoles();

    /**
     * @param  string                    $username
     * @return ImapUserManagerInterface
     */
    function setUsername($username);

    /**
     * @param  string                    $password
     * @return ImapUserManagerInterface
     */
    function setPassword($password);

    /**
     * @param  string   $username
     * @return boolean
     */
    function supports($username);
}
