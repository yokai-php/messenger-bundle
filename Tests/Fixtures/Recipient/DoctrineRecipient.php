<?php

namespace Yokai\MessengerBundle\Tests\Fixtures\Recipient;

use Yokai\MessengerBundle\Recipient\DoctrineRecipientInterface;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class DoctrineRecipient implements DoctrineRecipientInterface
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
