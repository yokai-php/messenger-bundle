<?php

namespace Yokai\MessengerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Yokai\MessengerBundle\DependencyInjection\Factory\MessageDefinitionFactory;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class YokaiMessengerExtension extends Extension
{
    /**
     * @inheritdoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(
            $this->getConfiguration($configs, $container),
            $configs
        );

        $container->setParameter(
            'yokai_messenger.content_builder_defaults',
            $config['content_builder']
        );
        $container->setParameter(
            'yokai_messenger.logging_channel',
            $config['logging_channel']
        );

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $swiftmailerEnabled = $config['channels']['swiftmailer']['enabled'] &&
                              class_exists('Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle');
        $doctrineEnabled = $config['channels']['doctrine']['enabled'] &&
                           class_exists('Doctrine\Bundle\DoctrineBundle\DoctrineBundle');
        $mobileEnabled = $config['channels']['mobile']['enabled'] &&
                         class_exists('Sly\NotificationPusher\NotificationPusher');

        $container->setParameter('yokai_messenger.swiftmailer_enabled', $swiftmailerEnabled);
        $container->setParameter('yokai_messenger.doctrine_enabled', $doctrineEnabled);
        $container->setParameter('yokai_messenger.mobile_enabled', $mobileEnabled);

        if ($swiftmailerEnabled) {
            $this->registerSwiftmailer($config['channels']['swiftmailer'], $container, $loader);
        }
        if ($doctrineEnabled) {
            $this->registerDoctrine($config['channels']['doctrine'], $container, $loader);
        }
        if ($mobileEnabled) {
            $this->registerMobile($config['channels']['mobile'], $container, $loader);
        }

        $this->registerMessages($config['messages'], $container);
    }

    /**
     * @param array            $config
     * @param ContainerBuilder $container
     * @param XmlFileLoader    $loader
     */
    private function registerSwiftmailer(array $config, ContainerBuilder $container, XmlFileLoader $loader)
    {
        $container->setParameter(
            'yokai_messenger.swiftmailer_channel_defaults',
            [
                'from' => $config['from'],
                'translator_catalog' => $config['translator_catalog'],
            ]
        );
        $loader->load('swiftmailer.xml');
    }

    /**
     * @param array            $config
     * @param ContainerBuilder $container
     * @param XmlFileLoader    $loader
     */
    private function registerDoctrine(array $config, ContainerBuilder $container, XmlFileLoader $loader)
    {
        $container->setAlias(
            'yokai_messenger.doctrine_channel_manager',
            sprintf(
                'doctrine.orm.%s_entity_manager',
                $config['manager']
            )
        );
        $loader->load('doctrine.xml');
    }

    /**
     * @param array            $config
     * @param ContainerBuilder $container
     * @param XmlFileLoader    $loader
     */
    private function registerMobile(array $config, ContainerBuilder $container, XmlFileLoader $loader)
    {
        $apnsEnabled = $config['apns']['enabled'] && class_exists('Sly\NotificationPusher\Adapter\Apns');
        $gcmEnabled = $config['gcm']['enabled'] && class_exists('Sly\NotificationPusher\Adapter\Gcm');

        if (!$apnsEnabled && !$gcmEnabled) {
            return;
        }

        $container->setParameter('yokai_messenger.mobile.push_manager.environment', $config['environment']);

        $loader->load('mobile.xml');

        if ($apnsEnabled) {
            $loader->load('mobile/apns.xml');
            $container->setParameter('yokai_messenger.mobile.apns_adapter.certificate', $config['apns']['certificate']);
            $container->setParameter('yokai_messenger.mobile.apns_adapter.pass_phrase', $config['apns']['pass_phrase']);
        }
        if ($gcmEnabled) {
            $loader->load('mobile/gcm.xml');
            $container->setParameter('yokai_messenger.mobile.gcm_adapter.api_key', $config['gcm']['api_key']);
        }
    }

    /**
     * @param array            $config
     * @param ContainerBuilder $container
     */
    private function registerMessages(array $config, ContainerBuilder $container)
    {
        foreach ($config as $messageConfig) {
            MessageDefinitionFactory::create(
                $container,
                $messageConfig['id'],
                $messageConfig['channels'],
                $messageConfig['defaults'],
                $messageConfig['options']
            );
        }
    }
}
