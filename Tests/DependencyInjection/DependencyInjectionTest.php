<?php

namespace Yokai\MessengerBundle\Tests\DependencyInjection;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Yokai\MessengerBundle\Message;
use Yokai\MessengerBundle\Tests\Fixtures\Channel\DummyChannel;
use Yokai\MessengerBundle\Tests\Fixtures\Channel\InvalidChannel;
use Yokai\MessengerBundle\Tests\Fixtures\InvalidMessage;
use Yokai\MessengerBundle\YokaiMessengerBundle;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class DependencyInjectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    protected function setUp()
    {
        $bundle = new YokaiMessengerBundle();
        $this->container = new ContainerBuilder();

        $bundles = [
            'FrameworkBundle' => 'Symfony\Bundle\FrameworkBundle\FrameworkBundle',
            'SwiftmailerBundle' => 'Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle',
            'DoctrineBundle' => 'Doctrine\Bundle\DoctrineBundle\DoctrineBundle',
            'YokaiMessengerBundle' => 'Yokai\MessengerBundle\YokaiMessengerBundle',
            'AppBundle' => 'AppBundle\AppBundle',
        ];

        $this->container->setParameter('kernel.debug', true);
        $this->container->setParameter('kernel.bundles', $bundles);
        $this->container->set('templating', $this->prophesize(EngineInterface::class)->reveal());
        $this->container->set('translator', $this->prophesize(TranslatorInterface::class)->reveal());
        $this->container->set('logger', $this->prophesize(LoggerInterface::class)->reveal());
        $this->container->set('mailer', $this->prophesize(\Swift_Mailer::class)->reveal());
        $this->container->setDefinition('doctrine.orm.default_entity_manager', new Definition(EntityManager::class));
        $this->container->setDefinition('doctrine.orm.default_metadata_driver', new Definition(MappingDriverChain::class));
        $this->container->setDefinition('doctrine.orm.default_configuration', new Definition(Configuration::class));
        $this->container->setParameter('doctrine.default_entity_manager', 'default');

        $this->container->registerExtension($bundle->getContainerExtension());
        $bundle->build($this->container);
    }

    public function testNothingIsRegisteredWithoutConfiguration()
    {
        $this->container->compile();

        $this->assertFalse($this->container->has('yokai_messenger.sender'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Service "test_channel" must implement interface "Yokai\MessengerBundle\Channel\ChannelInterface".
     */
    public function testRegisteredChannelsShouldImplementInterface()
    {
        $definition = new Definition(InvalidChannel::class);
        $definition->addTag('yokai_messenger.channel');
        $this->container->setDefinition('test_channel', $definition);

        $this->loadConfiguration($this->container, 'none.yml');
        $this->container->compile();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Service "test_channel" must define the "alias" attribute on "messenger.channel" tags.
     */
    public function testRegisteredChannelsShouldDeclareAnAlias()
    {
        $definition = new Definition(DummyChannel::class);
        $definition->addTag('yokai_messenger.channel');
        $this->container->setDefinition('test_channel', $definition);

        $this->loadConfiguration($this->container, 'none.yml');
        $this->container->compile();
    }

    public function testChannelsCanBeRegistered()
    {
        $definition = new Definition(DummyChannel::class);
        $definition->addTag('yokai_messenger.channel', ['alias' => 'test_1', 'priority' => 10]);
        $this->container->setDefinition('test_channel_1', $definition);

        $definition = new Definition(DummyChannel::class);
        $definition->addTag('yokai_messenger.channel', ['alias' => 'test_2']);
        $definition->addTag('yokai_messenger.channel', ['alias' => 'test_2.not_used']); //this tag will not be computed
        $this->container->setDefinition('test_channel_2', $definition);

        $this->loadConfiguration($this->container, 'none.yml');
        $this->container->compile();

        $calls = $this->container->getDefinition('yokai_messenger.sender')->getMethodCalls();

        $this->assertCount(2, $calls);

        $this->assertCallReferenceOrDefinition($calls[0], 'addChannel', 'test_channel_1');
        $this->assertSame('test_1', $calls[0][1][1]);
        $this->assertSame(10, $calls[0][1][2]);

        $this->assertCallReferenceOrDefinition($calls[1], 'addChannel', 'test_channel_2');
        $this->assertSame('test_2', $calls[1][1][1]);
        $this->assertSame(1, $calls[1][1][2]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Service "test_message" must be a "Yokai\MessengerBundle\Message".
     */
    public function testRegisteredMessagesShouldBeOfMessageClass()
    {
        $definition = new Definition(InvalidMessage::class);
        $definition->addTag('yokai_messenger.message');
        $this->container->setDefinition('test_message', $definition);

        $this->loadConfiguration($this->container, 'none.yml');
        $this->container->compile();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Service "test_message" must define the "channel" attribute on "messenger.message" tags.
     */
    public function testRegisteredMessagesDeclareOnWhichChannelItShouldBeHandled()
    {
        $definition = new Definition(Message::class);
        $definition->addTag('yokai_messenger.message');
        $this->container->setDefinition('test_message', $definition);

        $this->loadConfiguration($this->container, 'none.yml');
        $this->container->compile();
    }

    public function testMessagesCanBeRegisteredOnManyChannels()
    {
        $definition = new Definition(Message::class);
        $definition->addTag('yokai_messenger.message', ['channel' => 'foo']);
        $definition->addTag('yokai_messenger.message', ['channel' => 'bar']);
        $this->container->setDefinition('test_message', $definition);

        $this->loadConfiguration($this->container, 'none.yml');
        $this->container->compile();

        $calls = $this->container->getDefinition('yokai_messenger.sender')->getMethodCalls();

        $this->assertCount(2, $calls);

        $this->assertCallReferenceOrDefinition($calls[0], 'addMessage', 'test_message');
        $this->assertSame('foo', $calls[0][1][1]);

        $this->assertCallReferenceOrDefinition($calls[1], 'addMessage', 'test_message');
        $this->assertSame('bar', $calls[1][1][1]);
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $resource
     */
    protected function loadConfiguration(ContainerBuilder $container, $resource)
    {
        $locator = new FileLocator(dirname(__DIR__).'/Fixtures/Extension/');
        $path = $locator->locate($resource);

        switch (pathinfo($path, PATHINFO_EXTENSION)) {
            case 'yml':
                $loader = new Loader\YamlFileLoader($container, $locator);
                break;

            //todo nice to have : support more configuration format

            default:
                throw new \InvalidArgumentException('File ' . $path . ' is not supported.');
                break;
        }

        $loader->load($resource);
    }

    /**
     * @dataProvider configurationProvider
     *
     * @param string  $resource
     * @param array   $enabledChannels
     * @param array   $disabledChannels
     * @param array   $parameters
     */
    public function testConfiguration($resource, array $enabledChannels, array $disabledChannels, array $parameters)
    {
        $this->loadConfiguration($this->container, $resource);
        $this->container->compile();

        $channels = $this->container->findTaggedServiceIds('yokai_messenger.channel');

        foreach ($enabledChannels as $channel) {
            $this->assertTrue($this->container->has('yokai_messenger.' . $channel . '_channel'), $channel.' channel not enabled');
            $this->assertArrayHasKey('yokai_messenger.' . $channel . '_channel', $channels);
        }

        foreach ($disabledChannels as $channel) {
            $this->assertFalse($this->container->has('yokai_messenger.' . $channel . '_channel', $channel.' channel not disabled'));
            $this->assertArrayNotHasKey('yokai_messenger.' . $channel . '_channel', $channels);
        }

        foreach ($parameters as $name => $value) {
            $this->assertTrue($this->container->hasParameter($name), $name.' parameter not found');
            $this->assertSame($value, $this->container->getParameter($name));
        }
    }

    /**
     * @param array  $call
     * @param string $method
     * @param string $id
     */
    public function assertCallReferenceOrDefinition(array $call, $method, $id)
    {
        $this->assertSame($method, $call[0]);
        $this->assertThat(
            $call[1][0],
            $this->logicalOr(
                $this->isInstanceOf(Reference::class),
                $this->isInstanceOf(Definition::class)
            )
        );
        if ($call[1][0] instanceof Reference) {
            $this->assertSame($id, (string) $call[1][0]);
        }
    }

    public function configurationProvider()
    {
        $sets = [];
        foreach ($this->formatProvider() as $format) {
            $format = $format[0];
            $sets[] = [
                'swiftmailer_only.' . $format,
                ['swiftmailer'],
                ['doctrine', 'mobile', 'twilio'],
                [
                    'yokai_messenger.swiftmailer_channel_defaults' => [
                        'from' => ['no-reply@acme.org' => 'NoReply', 'no-reply2@acme.org' => 'NoReply2'],
                        'translator_catalog' => 'messaging'
                    ]
                ],
            ];
            $sets[] = [
                'doctrine_only.' . $format,
                ['doctrine'],
                ['swiftmailer', 'mobile', 'twilio'],
                []
            ];
            $sets[] = [
                'mobile_only.' . $format,
                ['mobile'],
                ['swiftmailer', 'doctrine', 'twilio'],
                [
                    'yokai_messenger.mobile.apns_adapter.certificate' => '/path/to/your/apns-certificate.pem',
                    'yokai_messenger.mobile.apns_adapter.pass_phrase' => 'example',
                    'yokai_messenger.mobile.gcm_adapter.api_key' => 'YourApiKey'
                ]
            ];
            $sets[] = [
                'twilio_only.' . $format,
                ['twilio'],
                ['swiftmailer', 'doctrine', 'mobile'],
                [
                    'yokai_messenger.twilio_channel_defaults' => [
                        'from' => '+330601020304',
                        'api_id' => 'azertyuiop',
                        'api_token' => 'qsdfghjklm',
                    ],
                ]
            ];
            $sets[] = [
                'all.' . $format,
                ['doctrine', 'swiftmailer', 'mobile', 'twilio'],
                [],
                [
                    'yokai_messenger.swiftmailer_channel_defaults' => [
                        'from' => ['no-reply@acme.org', 'no-reply@acme.org'],
                        'translator_catalog' => 'messaging'
                    ],
                    'yokai_messenger.twilio_channel_defaults' => [
                        'from' => '+330601020304',
                        'api_id' => 'azertyuiop',
                        'api_token' => 'qsdfghjklm',
                    ],
                    'yokai_messenger.mobile.apns_adapter.certificate' => '/path/to/your/apns-certificate.pem',
                    'yokai_messenger.mobile.apns_adapter.pass_phrase' => 'example',
                    'yokai_messenger.mobile.gcm_adapter.api_key' => 'YourApiKey'
                ],
            ];
            $sets[] = [
                'none.' . $format,
                [],
                ['doctrine', 'swiftmailer', 'mobile'],
                []
            ];
            $sets[] = [
                'full.' . $format,
                ['doctrine', 'swiftmailer', 'mobile', 'twilio'],
                [],
                [
                    'yokai_messenger.swiftmailer_channel_defaults' => [
                        'from' => ['no-reply@acme.org'],
                        'translator_catalog' => 'messaging'
                    ],
                    'yokai_messenger.twilio_channel_defaults' => [
                        'from' => '+330601020304',
                        'api_id' => 'azertyuiop',
                        'api_token' => 'qsdfghjklm',
                    ],
                    'yokai_messenger.mobile.apns_adapter.certificate' => '/path/to/your/apns-certificate.pem',
                    'yokai_messenger.mobile.apns_adapter.pass_phrase' => 'example',
                    'yokai_messenger.mobile.gcm_adapter.api_key' => 'YourApiKey'
                ]
            ];
        }

        return $sets;
    }

    /**
     * @dataProvider formatProvider
     */
    public function testMessagesLoadedWithConfiguration($format)
    {
        $this->loadConfiguration($this->container, 'full.' . $format);
        $this->container->compile();

        $calls = $this->container->getDefinition('yokai_messenger.sender')->getMethodCalls();
        $calls = array_filter(
            $calls,
            function ($call) {
                return $call[0] === 'addMessage';
            }
        );
        $calls = array_values($calls);

        $this->assertCount(5, $calls);

        $this->assertInstanceOf(Definition::class, $calls[0][1][0]);
        $this->assertSame('foo', $calls[0][1][0]->getArgument(0));
        $this->assertSame('swiftmailer', $calls[0][1][1]);

        $this->assertInstanceOf(Definition::class, $calls[1][1][0]);
        $this->assertSame('bar', $calls[1][1][0]->getArgument(0));
        $this->assertSame('doctrine', $calls[1][1][1]);

        $this->assertInstanceOf(Definition::class, $calls[2][1][0]);
        $this->assertSame('baz', $calls[2][1][0]->getArgument(0));
        $this->assertSame('swiftmailer', $calls[2][1][1]);

        $this->assertInstanceOf(Definition::class, $calls[3][1][0]);
        $this->assertSame('baz', $calls[3][1][0]->getArgument(0));
        $this->assertSame('doctrine', $calls[3][1][1]);

        $this->assertInstanceOf(Definition::class, $calls[4][1][0]);
        $this->assertSame('baz', $calls[4][1][0]->getArgument(0));
        $this->assertSame('mobile', $calls[4][1][1]);
    }

    public function formatProvider()
    {
        return [
            ['yml']
        ];
    }
}
