<?php

namespace Yokai\MessengerBundle\Exception;

use InvalidArgumentException;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface as OptionsResolverException;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class BadConfigurationException extends InvalidArgumentException implements ExceptionInterface
{
    /**
     * @param OptionsResolverException $previous
     *
     * @return BadConfigurationException
     */
    public static function create(OptionsResolverException $previous)
    {
        return new self(
            sprintf(
                'The provided configuration is invalid. Exception : %s - %s',
                get_class($previous),
                $previous->getMessage()
            ),
            0,
            $previous
        );
    }
}
