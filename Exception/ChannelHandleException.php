<?php

namespace Yokai\MessengerBundle\Exception;

use Exception;
use RuntimeException;
use Yokai\MessengerBundle\Channel\ChannelInterface;
use Yokai\MessengerBundle\Delivery;
use Yokai\MessengerBundle\Message;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class ChannelHandleException extends RuntimeException implements ExceptionInterface
{
    /**
     * @param ChannelInterface $channel
     * @param Delivery         $delivery
     * @param Exception       $previous
     *
     * @return ChannelHandleException
     */
    public static function createOnException(ChannelInterface $channel, Delivery $delivery, Exception $previous)
    {
        return new self(
            sprintf(
                'An error has occurred during the notification process of message "%s" for channel "%s".'
                .' Exception : %s - %s',
                $delivery->getMessage(),
                get_class($channel),
                get_class($previous),
                $previous->getMessage()
            ),
            0,
            $previous
        );
    }

    /**
     * @param ChannelInterface $channel
     * @param Message          $message
     * @param mixed            $recipient
     *
     * @return ChannelHandleException
     */
    public static function unsupportedRecipient(ChannelInterface $channel, Message $message, $recipient)
    {
        return new self(
            sprintf(
                'The recipient "%s" is not supported by channel "%s" for message "%s".',
                is_object($recipient) ? get_class($recipient) : gettype($recipient),
                get_class($channel),
                $message->getId()
            )
        );
    }
}
