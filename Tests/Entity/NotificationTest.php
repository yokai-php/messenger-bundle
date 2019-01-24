<?php

namespace Yokai\MessengerBundle\Tests\Entity;

use Yokai\MessengerBundle\Entity\Notification;
use Yokai\MessengerBundle\Tests\Fixtures\Recipient\IdentifierRecipient;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class NotificationTest extends \PHPUnit_Framework_TestCase
{
    public function testPropertiesAssignment()
    {
        $notification = new Notification(
            'subject',
            'body',
            new IdentifierRecipient('1')
        );

        $this->assertSame(null, $notification->getId());
        $this->assertSame('subject', $notification->getSubject());
        $this->assertSame('body', $notification->getBody());
        $this->assertSame(IdentifierRecipient::class, $notification->getRecipientClass());
        $this->assertSame('1', $notification->getRecipientId());
        $this->assertInstanceOf(\DateTime::class, $notification->getRecordedAt());

        $this->assertNull(null, $notification->getDeliveredAt());
        $this->assertFalse($notification->isDelivered());
        $notification->setDelivered();
        $this->assertInstanceOf(\DateTime::class, $notification->getDeliveredAt());
        $this->assertTrue($notification->isDelivered());
    }

    public function testImmutability()
    {
        $notification = new Notification(
            'subject',
            'body',
            new IdentifierRecipient('1')
        );

        $notification->setDelivered();
        $date = $notification->getDeliveredAt();
        $notification->setDelivered();
        $this->assertSame($date, $notification->getDeliveredAt());
    }
}
