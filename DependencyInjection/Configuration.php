<?php

namespace MessengerBundle\DependencyInjection;

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
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();
        $root = $builder->root($this->name);

        $root
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('logging_channel')->defaultValue('main')->end()
                ->append($this->createContentBuilderNode())
                ->arrayNode('channels')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->append($this->createSwiftmailerChannelNode())
                        ->append($this->createDoctrineChannelNode())
                    ->end()
                ->end()
            ->end()
        ;

        return $builder;
    }

    /**
     * @return NodeDefinition
     */
    private function createSwiftmailerChannelNode()
    {
        $builder = new TreeBuilder();
        $swiftmailer = $builder->root('swiftmailer');

        $swiftmailer
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

        return $swiftmailer;
    }

    /**
     * @return NodeDefinition
     */
    private function createDoctrineChannelNode()
    {
        $builder = new TreeBuilder();
        $doctrine = $builder->root('doctrine');

        $doctrine
            ->canBeEnabled()
            ->children()
                ->scalarNode('manager')->defaultValue('default')->end()
            ->end()
        ;

        return $doctrine;
    }

    /**
     * @return NodeDefinition
     */
    private function createContentBuilderNode()
    {
        $builder = new TreeBuilder();
        $contentBuilder = $builder->root('content_builder');

        $contentBuilder
            ->defaultValue([])
            ->useAttributeAsKey('name')
            ->prototype('variable')
            ->end()
        ;

        return $contentBuilder;
    }
}
