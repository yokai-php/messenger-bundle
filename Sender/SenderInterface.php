<?php

namespace Yokai\MessengerBundle\Sender;

/**
 * @author Yann Eugoné <yann.eugone@gmail.com>
 */
interface SenderInterface
{
    /**
     * @param string $message
     * @param mixed  $recipient
     * @param array  $parameters
     * @param array  $attachments
     */
    public function send($message, $recipient, array $parameters = [], array $attachments = []);
}
