<?php

namespace Bluemesa\Bundle\ImapAuthenticationBundle\Event;

final class ImapEvents
{
    const PRE_BIND = 'bluemesa_imap.security.authentication.pre_bind';
    const POST_BIND = 'bluemesa_imap.security.authentication.post_bind';
}
