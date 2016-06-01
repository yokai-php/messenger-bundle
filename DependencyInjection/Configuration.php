<?php

namespace Yokai\MessengerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Yann Eugoné <yann.eugone@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var TreeBuilder
     */
    private $builder;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->builder = new TreeBuilder();
    }

    /**
     * @inheritdoc
     */
    public function getConfigTreeBuilder()
    {
        $builder = $this->getBuilder();
        $root = $builder->root($this->name);

        $root
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('logging_channel')->defaultValue('app')->end()
                ->append($this->getContentBuilderNode())
                ->append($this->getChannelsNode())
            ->end()
        ;

        return $builder;
    }

    /**
     * @return TreeBuilder
     */
    private function getBuilder()
    {
        return clone $this->builder;
    }

    /**
     * @return NodeDefinition
     */
    private function getChannelsNode()
    {
        $node = $this->getBuilder()->root('channels');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->append($this->getSwiftmailerChannelNode())
                ->append($this->getDoctrineChannelNode())
            ->end()
        ;

        return $node;
    }

    /**
     * @return NodeDefinition
     */
    private function getSwiftmailerChannelNode()
    {
        $node = $this->getBuilder()->root('swiftmailer');

        $node
            ->canBeEnabled()
            ->validate()
                ->ifTrue(function ($value) {
                    if (!$value['enabled']) {
                        return false;
                    }
                    if (isset($value['from_addr']) & !empty($value['from_addr'])) {
                        return false;
                    }

                    return true;
                })
                ->thenInvalid('The child node "from_addr" at path "messenger.channels.swiftmailer" must be configured.')
            ->end()
            ->children()
                ->scalarNode('from_addr')
                    ->defaultNull()
                ->end()
                ->scalarNode('translator_catalog')->defaultValue('notifications')->end()
            ->end()
        ;

        return $node;
    }

    /**
     * @return NodeDefinition
     */
    private function getDoctrineChannelNode()
    {
        $node = $this->getBuilder()->root('doctrine');

        $node
            ->canBeEnabled()
            ->children()
                ->scalarNode('manager')->defaultValue('default')->end()
            ->end()
        ;

        return $node;
    }

    /**
     * @return NodeDefinition
     */
    private function getContentBuilderNode()
    {
        $node = $this->getBuilder()->root('content_builder');

        $node
            ->defaultValue([])
            ->useAttributeAsKey('name')
            ->prototype('variable')
            ->end()
        ;

        return $node;
    }
}
