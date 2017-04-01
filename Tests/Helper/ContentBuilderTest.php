<?php

namespace Yokai\MessengerBundle\Tests\Helper;

use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Yokai\MessengerBundle\Helper\ContentBuilder;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class ContentBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $templating;

    /**
     * @var ObjectProphecy
     */
    private $translator;

    protected function setUp()
    {
        $this->templating = $this->prophesize(EngineInterface::class);
        $this->translator = $this->prophesize(TranslatorInterface::class);
    }

    protected function tearDown()
    {
        unset(
            $this->templating,
            $this->translator
        );
    }

    protected function createHelper(array $defaults)
    {
        return new ContentBuilder(
            $this->templating->reveal(),
            $this->translator->reveal(),
            $defaults
        );
    }

    /**
     * @expectedException \Yokai\MessengerBundle\Exception\BadMethodCallException
     */
    public function testConfigureShouldBeCalledBeforeGetBody()
    {
        $helper = $this->createHelper([]);

        $helper->getBody([]);
    }

    /**
     * @expectedException \Yokai\MessengerBundle\Exception\BadMethodCallException
     */
    public function testConfigureShouldBeCalledBeforeGetSubject()
    {
        $helper = $this->createHelper([]);

        $helper->getSubject([]);
    }

    public function testPassingUnknownOptionsIsNotThrowingAnException()
    {
        $helper = $this->createHelper([]);

        $helper->configure([
            'subject' => 'subject',
            'template' => 'template',
            'translation_catalog' => 'messages',
            'option_that_do_not_exists' => 'unknown',
        ]);

        $this->assertTrue(true); // if we are here, any exception was throwed
    }

    public function testAffectingDefaults()
    {
        $helper = $this->createHelper([
            'subject' => 'subject',
            'template' => 'template',
            'translation_catalog' => 'messages',
            'option_that_do_not_exists' => 'unknown',
        ]);

        $helper->configure([]);

        $this->assertTrue(true); // if we are here, any exception was throwed
    }

    /**
     * @dataProvider subjectProvider
     */
    public function testBuildingSubject(array $options, array $parameters, $expectedSubject, array $expectedParameters)
    {
        $helper = $this->createHelper([
            'template' => 'template',
            'translation_catalog' => 'messages',
        ]);

        $helper->configure($options);

        $this->translator->trans($expectedSubject, $expectedParameters, 'messages')
            ->shouldBeCalled()
            ->willReturn('test ok');

        $this->assertSame('test ok', $helper->getSubject($parameters));
    }

    /**
     * @dataProvider bodyProvider
     */
    public function testBuildingBody(array $options, array $parameters, $expectedTemplate, $expectedParameters)
    {
        $helper = $this->createHelper([
            'subject' => 'subject',
            'translation_catalog' => 'messages',
        ]);

        $helper->configure($options);

        $this->templating->render($expectedTemplate, $expectedParameters)
            ->shouldBeCalled()
            ->willReturn('test ok');

        $this->assertSame('test ok', $helper->getBody($parameters));
    }

    public function subjectProvider()
    {
        return [
            [
                [
                    'subject' => 'Welcome !',
                    'subject_parameters' => [],
                ],
                [
                ],
                'Welcome !',
                [
                ]
            ],
            [
                [
                    'subject' => 'Welcome %name% !',
                    'subject_parameters' => ['%name%'],
                ],
                [
                    '%name%' => 'John',
                ],
                'Welcome %name% !',
                [
                    '%name%' => 'John',
                ]
            ],
            [
                [
                    'subject' => 'Welcome %name% !',
                    'subject_parameters' => ['%name%'],
                ],
                [
                    '%name%' => 'John',
                    '%last_name%' => 'Doe',
                ],
                'Welcome %name% !',
                [
                    '%name%' => 'John',
                ]
            ],
        ];
    }

    public function bodyProvider()
    {
        return [
            [
                [
                    'template' => ':hello:world.txt.twig',
                ],
                [
                ],
                ':hello:world.txt.twig',
                [
                ],
            ],
            [
                [
                    'template' => ':hello:name.txt.twig',
                    'template_vars' => [
                        'date' => '2015-11-12',
                    ],
                ],
                [
                    'name' => 'John Doe',
                ],
                ':hello:name.txt.twig',
                [
                    'name' => 'John Doe',
                    'date' => '2015-11-12',
                ],
            ],
            [
                [
                    'template' => ':hello:{greet}.txt.twig',
                    'template_parameters' => ['{greet}'],
                    'template_vars' => [
                        'date' => '2015-11-12',
                    ],
                ],
                [
                    'name' => 'John Doe',
                    '{greet}' => 'name',
                ],
                ':hello:name.txt.twig',
                [
                    'name' => 'John Doe',
                    'date' => '2015-11-12',
                    '{greet}' => 'name',
                ],
            ],
        ];
    }
}
