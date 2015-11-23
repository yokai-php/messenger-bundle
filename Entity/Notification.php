<?php

namespace MessengerBundle\Entity;

use Doctrine\Common\Util\ClassUtils;
use MessengerBundle\Recipient\DoctrineRecipientInterface;

/**
 * Notification Doctrine ORM entity
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
     * @var \DateTime
     */
    private $recordedAt;

    /**
     * @var string
     */
    private $recipientClass;

    /**
     * @var int
     */
    private $recipientId;

    /**
     * @var \DateTime|null
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
        $this->recordedAt = new \DateTime('now');
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
     * @return string
     */
    public function getRecipientClass()
    {
        return $this->recipientClass;
    }

    /**
     * @return int
     */
    public function getRecipientId()
    {
        return $this->recipientId;
    }

    /**
     * @return \DateTime
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
     * @return \DateTime|null
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

        $this->deliveredAt = new \DateTime('now');
    }
}

