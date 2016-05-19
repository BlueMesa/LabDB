<?php

namespace Bluemesa\Bundle\ImapAuthenticationBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Bluemesa\Bundle\ImapAuthenticationBundle\User\ImapUserInterface;

class ImapUserEvent extends Event
{
    /**
     * @var ImapUserInterface
     */
    private $user;

    /**
     * ImapUserEvent constructor
     *
     * @param ImapUserInterface $user
     */
    public function __construct(ImapUserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * @return ImapUserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param  ImapUserInterface $user
     * @return ImapUserEvent
     */
    public function setUser(ImapUserInterface $user)
    {
        $this->user = $user;

        return $this;
    }
}
