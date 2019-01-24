<?php

namespace Yokai\MessengerBundle\Tests\Fixtures\Recipient;

use Yokai\MessengerBundle\Recipient\PhoneRecipientInterface;

/**
 * @author Matthieu Crinquand <matthieu.crinquand@gmail.com>
 */
class PhoneRecipient implements PhoneRecipientInterface
{
    private $phone;

    public function __construct($phone)
    {
        $this->phone = $phone;
    }

    public function getPhone()
    {
        return $this->phone;
    }
}
