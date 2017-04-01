<?php

namespace Yokai\MessengerBundle\Tests\Fixtures\Recipient;

use Yokai\MessengerBundle\Recipient\SwiftmailerRecipientInterface;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class SwiftmailerRecipient implements SwiftmailerRecipientInterface
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
