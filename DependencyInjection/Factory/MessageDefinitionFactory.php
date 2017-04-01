<?php

namespace Yokai\MessengerBundle\DependencyInjection\Factory;

use LogicException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Yokai\MessengerBundle\Message;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class MessageDefinitionFactory
{
    /**
     * @param ContainerBuilder $container
     * @param string           $id
     * @param array            $channels
     * @param array            $defaults
     * @param array            $options
     */
    public static function create(
        ContainerBuilder $container,
        $id,
        array $channels,
        array $defaults = [],
        array $options = []
    ) {
        $messageId = sprintf('yokai_messenger.message.%s', $id);

        if ($container->hasDefinition($messageId)) {
            throw new LogicException(
                sprintf(
                    'The message with id "%s" was already registered (a service with id "%s" already exists)',
                    $id,
                    $messageId
                )
            );
        }

        $messageDefinition = new Definition(Message::class, [$id, $defaults]);
        $messageDefinition->setPublic(false);

        foreach ($channels as $channel) {
            $messageDefinition->addTag('yokai_messenger.message', ['channel' => $channel]);
        }

        foreach ($options as $channel => $channelOptions) {
            $messageDefinition->addMethodCall('setOptions', [$channel, $channelOptions]);
        }

        $container->setDefinition($messageId, $messageDefinition);
    }
}
