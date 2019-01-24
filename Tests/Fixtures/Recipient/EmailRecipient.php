<?php

namespace Yokai\MessengerBundle\Tests\Fixtures\Recipient;

use Yokai\MessengerBundle\Recipient\EmailRecipientInterface;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class EmailRecipient implements EmailRecipientInterface
{
    private $email;

    public function __construct($email)
    {
        $this->email = $email;
    }

    public function getEmail()
    {
        return $this->email;
    }
}
