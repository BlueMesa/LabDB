<?php

namespace Bluemesa\Bundle\ImapAuthenticationBundle\Manager;

use Monolog\Logger;

interface ImapConnectionInterface
{
    /**
     * ImapConnectionInterface constructor
     *
     * @param array  $params
     * @param Logger $logger
     */
    function __construct(array $params, Logger $logger);

    /**
     * @param  string $user
     * @param  string $password
     * @return true
     */
    function bind($user, $password = '');

    /**
     * @return array
     */
    function getParameters();

    /**
     * @param  int   $connection
     * @return string
     */
    function getHost($connection);

    /**
     * @param  int        $connection
     * @return int|string
     */
    function getPort($connection);

    /**
     * @param  int     $connection
     * @return boolean
     */
    function isSecure($connection);

    /**
     * @param  int     $connection
     * @return boolean
     */
    function isEncrypted($connection);

    /**
     * @param  int    $connection
     * @return string
     */
    function getEncryption($connection);

    /**
     * @param  int     $connection
     * @return boolean
     */
    function getValidateCert($connection);

    /**
     * @param  int $connection
     * @return int
     */
    function getNretries($connection);

    /**
     * @param  string  $user
     * @return boolean
     */
    function supports($user);
}
