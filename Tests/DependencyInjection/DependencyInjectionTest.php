<?php

namespace Yokai\MessengerBundle\Tests\DependencyInjection;

use Doctrine\ORM\EntityManager;
use Yokai\MessengerBundle\Message;
use Yokai\MessengerBundle\Tests\Fixtures\Channel\DummyChannel;
use Yokai\MessengerBundle\Tests\Fixtures\Channel\InvalidChannel;
use Yokai\MessengerBundle\Tests\Fixtures\InvalidMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Yokai\MessengerBundle\YokaiMessengerBundle;

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

        $this->container->setParameter('kernel.debug', true);
        $this->container->set('templating', $this->prophesize(EngineInterface::class)->reveal());
        $this->container->set('translator', $this->prophesize(TranslatorInterface::class)->reveal());
        $this->container->set('logger', $this->prophesize(LoggerInterface::class)->reveal());
        $this->container->set('mailer', $this->prophesize(\Swift_Mailer::class)->reveal());
        $this->container->setDefinition('doctrine.orm.default_entity_manager', new Definition(EntityManager::class));

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

        $this->assertSame('addChannel', $calls[0][0]);
        $this->assertInstanceOf(Reference::class, $calls[0][1][0]);
        $this->assertSame('test_channel_1', (string) $calls[0][1][0]);
        $this->assertSame('test_1', $calls[0][1][1]);
        $this->assertSame(10, $calls[0][1][2]);

        $this->assertSame('addChannel', $calls[1][0]);
        $this->assertInstanceOf(Reference::class, $calls[1][1][0]);
        $this->assertSame('test_channel_2', (string) $calls[1][1][0]);
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

        $this->assertSame('addMessage', $calls[0][0]);
        $this->assertInstanceOf(Reference::class, $calls[0][1][0]);
        $this->assertSame('test_message', (string) $calls[0][1][0]);
        $this->assertSame('foo', $calls[0][1][1]);

        $this->assertSame('addMessage', $calls[1][0]);
        $this->assertInstanceOf(Reference::class, $calls[1][1][0]);
        $this->assertSame('test_message', (string) $calls[1][1][0]);
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
     */
    public function testConfiguration($resource, array $enabledChannels, array $disabledChannels)
    {
        $this->loadConfiguration($this->container, $resource);
        $this->container->compile();

        $channels = $this->container->findTaggedServiceIds('yokai_messenger.channel');

        foreach ($enabledChannels as $channel) {
            $this->assertTrue($this->container->has('yokai_messenger.' . $channel . '_channel'));
            $this->assertArrayHasKey('yokai_messenger.' . $channel . '_channel', $channels);
        }

        foreach ($disabledChannels as $channel) {
            $this->assertFalse($this->container->has('yokai_messenger.' . $channel . '_channel'));
            $this->assertArrayNotHasKey('yokai_messenger.' . $channel . '_channel', $channels);
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
                ['doctrine'],
            ];
            $sets[] = [
                'doctrine_only.' . $format,
                ['doctrine'],
                ['swiftmailer'],
            ];
            $sets[] = [
                'all.' . $format,
                ['doctrine', 'swiftmailer'],
                [],
            ];
            $sets[] = [
                'none.' . $format,
                [],
                ['doctrine', 'swiftmailer'],
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

        $this->assertCount(4, $calls);

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
    }

    public function formatProvider()
    {
        return [
            ['yml']
        ];
    }
}
