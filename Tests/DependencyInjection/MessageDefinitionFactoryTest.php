<?php

namespace Yokai\MessengerBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Yokai\MessengerBundle\DependencyInjection\Factory\MessageDefinitionFactory;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class MessageDefinitionFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \LogicException
     */
    public function testMessageCannotBeRegisteredMoreThanOnce()
    {
        $container = new ContainerBuilder();

        MessageDefinitionFactory::create(
            $container,
            'foo',
            ['swiftmailer'],
            ['foo' => 'FOO'],
            []
        );

        MessageDefinitionFactory::create(
            $container,
            'foo',
            ['swiftmailer'],
            ['foo' => 'FOO'],
            []
        );
    }

    public function testCreateMessage()
    {
        $container = new ContainerBuilder();

        MessageDefinitionFactory::create(
            $container,
            'foo',
            ['swiftmailer'],
            ['foo' => 'FOO'],
            []
        );

        MessageDefinitionFactory::create(
            $container,
            'bar',
            ['swiftmailer', 'doctrine'],
            ['foo' => 'FOO'],
            ['swiftmailer' => ['bar' => 'BAR swiftmailer'], 'doctrine' => ['bar' => 'BAR doctrine']]
        );

        $this->assertTrue($container->hasDefinition('yokai_messenger.message.foo'));

        //Assert foo message is registered
        $foo = $container->getDefinition('yokai_messenger.message.foo');
        $this->assertSame('foo', $foo->getArgument(0));
        $this->assertSame(['foo' => 'FOO'], $foo->getArgument(1));

        $fooTags = $foo->getTag('yokai_messenger.message');
        $this->assertCount(1, $fooTags);
        $this->assertSame('swiftmailer', $fooTags[0]['channel']);

        $fooCalls = $foo->getMethodCalls();
        $this->assertCount(0, $fooCalls);


        //Assert bar message is registered
        $this->assertTrue($container->hasDefinition('yokai_messenger.message.bar'));

        $bar = $container->getDefinition('yokai_messenger.message.bar');
        $this->assertSame('bar', $bar->getArgument(0));
        $this->assertSame(['foo' => 'FOO'], $bar->getArgument(1));

        $barTags = $bar->getTag('yokai_messenger.message');
        $this->assertCount(2, $barTags);
        $this->assertSame('swiftmailer', $barTags[0]['channel']);
        $this->assertSame('doctrine', $barTags[1]['channel']);

        $barCalls = $bar->getMethodCalls();
        $this->assertCount(2, $barCalls);
        $this->assertSame('setOptions', $barCalls[0][0]);
        $this->assertSame('swiftmailer', $barCalls[0][1][0]);
        $this->assertSame(['bar' => 'BAR swiftmailer'], $barCalls[0][1][1]);
        $this->assertSame('setOptions', $barCalls[1][0]);
        $this->assertSame('doctrine', $barCalls[1][1][0]);
        $this->assertSame(['bar' => 'BAR doctrine'], $barCalls[1][1][1]);
    }
}
