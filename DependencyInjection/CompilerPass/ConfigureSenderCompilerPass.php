<?php

namespace MessengerBundle\DependencyInjection\CompilerPass;

use MessengerBundle\Channel\ChannelInterface;
use MessengerBundle\Message;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Yann Eugoné <yann.eugone@gmail.com>
 */
class ConfigureSenderCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('messenger.sender') && !$container->hasAlias('messenger.sender')) {
            return;
        }

        $definition = $container->findDefinition('messenger.sender');

        $this->registerChannels($definition, $container);
        $this->registerMessages($definition, $container);
    }

    /**
     * @param Definition       $definition
     * @param ContainerBuilder $container
     */
    private function registerChannels(Definition $definition, ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds('messenger.channel') as $id => $config) {
            $refClass = new \ReflectionClass($container->getDefinition($id)->getClass());
            if (!$refClass->implementsInterface(ChannelInterface::class)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Service "%s" must implement interface "%s".',
                        $id,
                        ChannelInterface::class
                    )
                );
            }

            if (!isset($config[0]['alias'])) {
                throw new \InvalidArgumentException(
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
                    isset($config[0]['priority']) ? $config[0]['priority'] : 1
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
        foreach ($container->findTaggedServiceIds('messenger.message') as $id => $config) {
            if (Message::class !== $container->getDefinition($id)->getClass()) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Service "%s" must be a "%s".',
                        $id,
                        Message::class
                    )
                );
            }

            foreach ($config as $attributes) {
                if (!isset($attributes['channel'])) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Service "%s" must define the "channel" attribute on "messenger.message" tags.',
                            $id
                        )
                    );
                }

                $definition->addMethodCall(
                    'addMessage',
                    [
                        new Reference($id),
                        $attributes['channel']
                    ]
                );
            }
        }
    }
}
