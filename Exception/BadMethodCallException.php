<?php

namespace Yokai\MessengerBundle\Exception;

use BadMethodCallException as BaseException;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class BadMethodCallException extends BaseException implements ExceptionInterface
{
    /**
     * @param string $methodThatShouldBeCalled
     * @param string $methodCalled
     *
     * @return BadMethodCallException
     */
    public static function createMissingCall($methodThatShouldBeCalled, $methodCalled)
    {
        return new self(
            sprintf(
                'The "%s" method must be called before calling "%s".',
                $methodThatShouldBeCalled,
                $methodCalled
            )
        );
    }

    /**
     * @param string $channel
     *
     * @return BadMethodCallException
     */
    public static function createMissingChannel($channel)
    {
        return new self(
            sprintf(
                'The "%s" channel was never registered.',
                $channel
            )
        );
    }

    /**
     * @param string $message
     *
     * @return BadMethodCallException
     */
    public static function createMissingMessage($message)
    {
        return new self(
            sprintf(
                'The "%s" channel was never registered.',
                $message
            )
        );
    }
}
