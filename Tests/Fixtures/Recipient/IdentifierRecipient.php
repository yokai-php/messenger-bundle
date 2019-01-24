<?php

namespace Yokai\MessengerBundle\Tests\Fixtures\Recipient;

use Yokai\MessengerBundle\Recipient\IdentifierRecipientInterface;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class IdentifierRecipient implements IdentifierRecipientInterface
{
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
}
