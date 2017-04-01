<?php

namespace Yokai\MessengerBundle\Entity;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class NotificationAttachment
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Notification
     */
    private $notification;

    /**
     * @var string
     */
    private $attachment;

    /**
     * @param Notification $notification
     * @param string       $attachment
     */
    public function __construct(Notification $notification, $attachment)
    {
        $this->attachment = $attachment;
        $this->notification = $notification;
    }

    /**
     * @return string
     */
    public function getAttachment()
    {
        return $this->attachment;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Notification
     */
    public function getNotification()
    {
        return $this->notification;
    }
}
