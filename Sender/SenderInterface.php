<?php

namespace Yokai\MessengerBundle\Sender;

use Symfony\Component\HttpFoundation\File\File;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
interface SenderInterface
{
    /**
     * @param string $message
     * @param mixed  $recipient
     * @param array  $parameters
     * @param File[] $attachments
     */
    public function send($message, $recipient, array $parameters = [], array $attachments = []);
}
