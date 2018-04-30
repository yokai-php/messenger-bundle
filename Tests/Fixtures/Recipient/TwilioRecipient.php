<?php

namespace Yokai\MessengerBundle\Tests\Fixtures\Recipient;

use Yokai\MessengerBundle\Recipient\TwilioRecipientInterface;

/**
 * @author Matthieu Crinquand <matthieu.crinquand@gmail.com>
 */
class TwilioRecipient implements TwilioRecipientInterface
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
