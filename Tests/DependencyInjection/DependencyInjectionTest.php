<?php

namespace MessengerBundle\Tests\DependencyInjection;

use MessengerBundle\Message;
use MessengerBundle\MessengerBundle;
use MessengerBundle\Tests\Fixtures\Channel\DummyChannel;
use MessengerBundle\Tests\Fixtures\Channel\InvalidChannel;
use MessengerBundle\Tests\Fixtures\InvalidMessage;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

class DependencyInjectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    protected function setUp()
    {
        $bundle = new MessengerBundle();
        $this->container = new ContainerBuilder();

        $this->container->setParameter('kernel.debug', true);
        $this->container->set('templating', $this->prophesize(EngineInterface::class)->reveal());
        $this->container->set('translator', $this->prophesize(TranslatorInterface::class)->reveal());
        $this->container->set('logger', $this->prophesize(LoggerInterface::class)->reveal());
        $this->container->set('mailer', $this->prophesize(\Swift_Mailer::class)->reveal());
        $this->container->set('doctrine', $this->prophesize(RegistryInterface::class)->reveal());

        $this->container->registerExtension($bundle->getContainerExtension());
        $bundle->build($this->container);
    }

    public function testNothingIsRegisteredWithoutConfiguration()
    {
        $this->container->compile();

        $this->assertFalse($this->container->has('messenger.sender'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Service "test_channel" must implement interface "MessengerBundle\Channel\ChannelInterface".
     */
    public function testRegisteredChannelsShouldImplementInterface()
    {
        $definition = new Definition(InvalidChannel::class);
        $definition->addTag('messenger.channel');
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
        $definition->addTag('messenger.channel');
        $this->container->setDefinition('test_channel', $definition);

        $this->loadConfiguration($this->container, 'none.yml');
        $this->container->compile();
    }

    public function testChannelsCanBeRegistered()
    {
        $definition = new Definition(DummyChannel::class);
        $definition->addTag('messenger.channel', ['alias' => 'test_1', 'priority' => 10]);
        $this->container->setDefinition('test_channel_1', $definition);

        $definition = new Definition(DummyChannel::class);
        $definition->addTag('messenger.channel', ['alias' => 'test_2']);
        $definition->addTag('messenger.channel', ['alias' => 'test_2.not_used']); //this tag will not be computed
        $this->container->setDefinition('test_channel_2', $definition);

        $this->loadConfiguration($this->container, 'none.yml');
        $this->container->compile();

        $calls = $this->container->getDefinition('messenger.sender')->getMethodCalls();

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
     * @expectedExceptionMessage Service "test_message" must be a "MessengerBundle\Message".
     */
    public function testRegisteredMessagesShouldBeOfMessageClass()
    {
        $definition = new Definition(InvalidMessage::class);
        $definition->addTag('messenger.message');
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
        $definition->addTag('messenger.message');
        $this->container->setDefinition('test_message', $definition);

        $this->loadConfiguration($this->container, 'none.yml');
        $this->container->compile();
    }

    public function testMessagesCanBeRegisteredOnManyChannels()
    {
        $definition = new Definition(Message::class);
        $definition->addTag('messenger.message', ['channel' => 'foo']);
        $definition->addTag('messenger.message', ['channel' => 'bar']);
        $this->container->setDefinition('test_message', $definition);

        $this->loadConfiguration($this->container, 'none.yml');
        $this->container->compile();

        $calls = $this->container->getDefinition('messenger.sender')->getMethodCalls();

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

        $channels = $this->container->findTaggedServiceIds('messenger.channel');

        foreach ($enabledChannels as $channel) {
            $this->assertTrue($this->container->has('messenger.' . $channel . '_channel'));
            $this->assertArrayHasKey('messenger.' . $channel . '_channel', $channels);
        }

        foreach ($disabledChannels as $channel) {
            $this->assertFalse($this->container->has('messenger.' . $channel . '_channel'));
            $this->assertArrayNotHasKey('messenger.' . $channel . '_channel', $channels);
        }
    }

    public function configurationProvider()
    {
        $sets = [];
        foreach (['yml'] as $format) {
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
}
