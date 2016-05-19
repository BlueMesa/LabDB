<?php

namespace Bluemesa\Bundle\ImapAuthenticationBundle\Manager;

use Bluemesa\Bundle\ImapAuthenticationBundle\Exception\ConnectionException;

class ImapUserManager implements ImapUserManagerInterface
{
    private $imapConnection;
    private $username;
    private $password;
    private $params;
    private $roles;

    /**
     * {@inheritDoc}
     */
    public function __construct(ImapConnectionInterface $conn)
    {
        $this->imapConnection = $conn;
        $this->params = $this->imapConnection->getParameters();
    }

    /**
     * {@inheritDoc}
     */
    public function auth()
    {
        if (strlen($this->password) === 0) {
            throw new ConnectionException('Password cannot be empty');
        }
        $this->bind();
        
        return TRUE;
    }

    /**
     * {@inheritDoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritDoc}
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * {@inheritDoc}
     */
    public function setUsername($username)
    {
        if ($username === "*") {
            throw new \InvalidArgumentException("Invalid username given.");
        }

        $this->username = $username;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($username)
    {
        return $this->imapConnection->supports($username);
    }

    /**
     * @return mixed
     */
    private function bind()
    {
        return $this->imapConnection->bind($this->username, $this->password);
    }
}
