<?php

namespace Yokai\MessengerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Yokai\MessengerBundle\DependencyInjection\Factory\MessageDefinitionFactory;
use Yokai\MessengerBundle\Sender\SenderInterface;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class YokaiMessengerExtension extends Extension
{
    /**
     * @var array
     */
    private $enabledChannelMap = [
        'swiftmailer' => [
            'class' => 'Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle',
            'bundle' => true,
        ],
        'twilio' => [
            'class' => 'Twilio\Rest\Api',
            'bundle' => false,
        ],
        'doctrine' => [
            'class' => 'Doctrine\Bundle\DoctrineBundle\DoctrineBundle',
            'bundle' => true,
        ],
        'mobile' => [
            'class' => 'Sly\NotificationPusher\NotificationPusher',
            'bundle' => false,
        ],
    ];

    /**
     * @inheritdoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);

        $container->setParameter('yokai_messenger.content_builder_defaults', $config['content_builder']);
        $container->setParameter('yokai_messenger.logging_channel', $config['logging_channel']);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $bundles = $container->getParameter('kernel.bundles');
        $enabledChannels = [];
        foreach ($this->enabledChannelMap as $channel => $enableConfig) {
            $enabled = true;
            if (!$config['channels'][$channel]['enabled']) {
                $enabled = false;
            }
            if (!class_exists($enableConfig['class'])) {
                $enabled = false;
            }
            if ($enableConfig['bundle'] && !in_array($enableConfig['class'], $bundles)) {
                $enabled = false;
            }

            $enabledChannels[$channel] = $enabled;
        }

        foreach ($enabledChannels as $channel => $enabled) {
            $container->setParameter('yokai_messenger.'.$channel.'_enabled', $enabled);
        }

        if ($enabledChannels['swiftmailer']) {
            $this->registerSwiftmailer($config['channels']['swiftmailer'], $container, $loader);
        }
        if ($enabledChannels['twilio']) {
            $this->registerTwilio($config['channels']['twilio'], $container, $loader);
        }
        if ($enabledChannels['doctrine']) {
            $this->registerDoctrine($config['channels']['doctrine'], $container, $loader);
        }
        if ($enabledChannels['mobile']) {
            $this->registerMobile($config['channels']['mobile'], $container, $loader);
        }

        $this->registerMessages($config['messages'], $container);

        $container->setAlias(SenderInterface::class, 'yokai_messenger.sender');
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
    private function registerTwilio(array $config, ContainerBuilder $container, XmlFileLoader $loader)
    {
        $container->setParameter(
            'yokai_messenger.twilio_channel_defaults',
            [
                'from' => $config['from'],
                'api_id' => $config['api_id'],
                'api_token' => $config['api_token'],
            ]
        );
        $loader->load('twilio.xml');
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

        $container->setParameter('yokai_messenger.load_doctrine_orm_mapping', true);
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
