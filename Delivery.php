<?php

namespace Yokai\MessengerBundle;

use Symfony\Component\HttpFoundation\File\File;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class Delivery
{
    /**
     * @var string
     */
    private $message;

    /**
     * @var mixed
     */
    private $recipient;

    /**
     * @var array
     */
    private $options;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $body;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var array
     */
    private $attachments;

    /**
     * @param string $message
     * @param mixed  $recipient
     * @param array  $options
     * @param string $subject
     * @param string $body
     * @param array  $parameters
     * @param File[] $attachments
     */
    public function __construct(
        $message,
        $recipient,
        array $options,
        $subject,
        $body,
        array $parameters,
        array $attachments
    ) {
        $this->message = $message;
        $this->recipient = $recipient;
        $this->options = $options;
        $this->subject = $subject;
        $this->body = $body;
        $this->parameters = $parameters;
        $this->attachments = $attachments;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return mixed
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
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
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return File[]
     */
    public function getAttachments()
    {
        return $this->attachments;
    }
}
