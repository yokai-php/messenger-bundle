<?php

namespace MessengerBundle\Tests\Fixtures\Recipient;

use MessengerBundle\Recipient\DoctrineRecipientInterface;

/**
 * @author Yann Eugoné <yann.eugone@gmail.com>
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
