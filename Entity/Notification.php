<?php

namespace Yokai\MessengerBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Util\ClassUtils;
use Yokai\MessengerBundle\Recipient\DoctrineRecipientInterface;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class Notification
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $body;

    /**
     * @var NotificationAttachment|Collection
     */
    private $attachments;

    /**
     * @var DateTime
     */
    private $recordedAt;

    /**
     * @var string
     */
    private $recipientClass;

    /**
     * @var string
     */
    private $recipientId;

    /**
     * @var DateTime|null
     */
    private $deliveredAt;

    /**
     * @param string                     $subject
     * @param string                     $body
     * @param DoctrineRecipientInterface $recipient
     */
    public function __construct($subject, $body, DoctrineRecipientInterface $recipient)
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->recipientClass = ClassUtils::getClass($recipient);
        $this->recipientId = $recipient->getId();
        $this->recordedAt = new DateTime('now');

        $this->attachments = new ArrayCollection();
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return NotificationAttachment[]
     */
    public function getAttachments()
    {
        return $this->attachments->toArray();
    }

    /**
     * @return string
     */
    public function getRecipientClass()
    {
        return $this->recipientClass;
    }

    /**
     * @return string
     */
    public function getRecipientId()
    {
        return $this->recipientId;
    }

    /**
     * @return DateTime
     */
    public function getRecordedAt()
    {
        return $this->recordedAt;
    }

    /**
     * @return bool
     */
    public function isDelivered()
    {
        return null !== $this->deliveredAt;
    }

    /**
     * @return DateTime|null
     */
    public function getDeliveredAt()
    {
        return $this->deliveredAt;
    }

    /**
     */
    public function setDelivered()
    {
        if (null !== $this->deliveredAt) {
            return; //immutable
        }

        $this->deliveredAt = new DateTime('now');
    }

    /**
     * @param NotificationAttachment $attachment
     */
    public function addNotificationAttachment(NotificationAttachment $attachment)
    {
        $this->attachments->add($attachment);
    }
}
