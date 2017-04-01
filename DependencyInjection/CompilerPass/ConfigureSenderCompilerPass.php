<?php

namespace Yokai\MessengerBundle\DependencyInjection\CompilerPass;

use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Yokai\MessengerBundle\Channel\ChannelInterface;
use Yokai\MessengerBundle\Message;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class ConfigureSenderCompilerPass implements CompilerPassInterface
{
    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('yokai_messenger.sender') && !$container->hasAlias('yokai_messenger.sender')) {
            return;
        }

        $definition = $container->findDefinition('yokai_messenger.sender');

        $this->registerChannels($definition, $container);
        $this->registerMessages($definition, $container);
    }

    /**
     * @param Definition       $definition
     * @param ContainerBuilder $container
     */
    private function registerChannels(Definition $definition, ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds('yokai_messenger.channel') as $id => $config) {
            if (!is_a($container->getDefinition($id)->getClass(), ChannelInterface::class, true)) {
                throw new InvalidArgumentException(
                    sprintf('Service "%s" must implement interface "%s".', $id, ChannelInterface::class)
                );
            }

            if (!isset($config[0]['alias'])) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Service "%s" must define the "alias" attribute on "messenger.channel" tags.',
                        $id
                    )
                );
            }

            $definition->addMethodCall(
                'addChannel',
                [
                    new Reference($id),
                    $config[0]['alias'],
                    isset($config[0]['priority']) ? $config[0]['priority'] : 1,
                ]
            );
        }
    }

    /**
     * @param Definition       $definition
     * @param ContainerBuilder $container
     */
    private function registerMessages(Definition $definition, ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds('yokai_messenger.message') as $id => $config) {
            if (Message::class !== $container->getDefinition($id)->getClass()) {
                throw new InvalidArgumentException(
                    sprintf('Service "%s" must be a "%s".', $id, Message::class)
                );
            }

            $messageReference = new Reference($id);

            foreach ($config as $attributes) {
                if (!isset($attributes['channel'])) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'Service "%s" must define the "channel" attribute on "messenger.message" tags.',
                            $id
                        )
                    );
                }

                $definition->addMethodCall('addMessage', [$messageReference, $attributes['channel']]);
            }
        }
    }
}
