<?php

namespace Yokai\MessengerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
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
            ->fixXmlConfig('message')
            ->children()
                ->scalarNode('logging_channel')->defaultValue('app')->end()
                ->append($this->getContentBuilderNode())
                ->append($this->getChannelsNode())
                ->append($this->getMessagesNode())
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
     * @param string $name
     *
     * @return ArrayNodeDefinition
     */
    private function root($name)
    {
        return $this->getBuilder()->root($name);
    }

    /**
     * @return NodeDefinition
     */
    private function getChannelsNode()
    {
        $node = $this->root('channels');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->append($this->getSwiftmailerChannelNode())
                ->append($this->getDoctrineChannelNode())
                ->append($this->getMobileChannelNode())
            ->end()
        ;

        return $node;
    }

    /**
     * @return NodeDefinition
     */
    private function getMessagesNode()
    {
        $node = $this->root('messages');

        $node
            ->defaultValue([])
            ->prototype('array')
                ->fixXmlConfig('channel')
                ->children()
                    ->scalarNode('id')->isRequired()->end()
                    ->arrayNode('channels')
                        ->beforeNormalization()
                            ->ifString()->then($this->stringToArray())
                        ->end()
                        ->requiresAtLeastOneElement()
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('defaults')
                        ->defaultValue([])
                        ->prototype('variable')->end()
                    ->end()
                    ->arrayNode('options')
                        ->defaultValue([])
                        ->useAttributeAsKey('channel')
                        ->prototype('variable')
                            ->validate()
                                ->ifTrue($this->isNotHash())
                                    ->thenInvalid('Expected a hash for channel options, got %s.')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * @return NodeDefinition
     */
    private function getSwiftmailerChannelNode()
    {
        $node = $this->root('swiftmailer');

        $node
            ->canBeEnabled()
            ->validate()
                ->ifTrue($this->nodeRequiredIfEnabled('from'))
                    ->thenInvalid($this->nodeMustBeConfigured('from', 'channels.swiftmailer'))
            ->end()
            ->children()
                ->arrayNode('from')
                    ->beforeNormalization()
                        ->ifString()->then($this->stringToArray())
                    ->end()
                    ->requiresAtLeastOneElement()
                    ->prototype('scalar')->end()
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
        $node = $this->root('doctrine');

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
    private function getMobileChannelNode()
    {
        $node = $this->root('mobile');

        $node
            ->canBeEnabled()
            ->children()
                ->scalarNode('environment')->defaultValue('dev')->end()
                ->arrayNode('apns')
                    ->canBeDisabled()
                    ->validate()
                        ->ifTrue($this->nodeRequiredIfEnabled('certificate'))
                            ->thenInvalid($this->nodeMustBeConfigured('certificate', 'channels.mobile.apns'))
                        ->ifTrue($this->nodeRequiredIfEnabled('certificate'))
                            ->thenInvalid($this->nodeMustBeConfigured('pass_phrase', 'channels.mobile.apns'))
                    ->end()
                    ->children()
                        ->scalarNode('certificate')->defaultNull()->end()
                        ->scalarNode('pass_phrase')->defaultNull()->end()
                    ->end()
                ->end()
                ->arrayNode('gcm')
                    ->canBeDisabled()
                    ->validate()
                        ->ifTrue($this->nodeRequiredIfEnabled('api_key'))
                            ->thenInvalid($this->nodeMustBeConfigured('api_key', 'channels.mobile.gcm'))
                    ->end()
                    ->children()
                        ->scalarNode('api_key')->defaultNull()->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * @return NodeDefinition
     */
    private function getContentBuilderNode()
    {
        $node = $this->root('content_builder');

        $node
            ->defaultValue([])
            ->useAttributeAsKey('name')
            ->prototype('variable')
            ->end()
        ;

        return $node;
    }

    /**
     * @return \Closure
     */
    private function stringToArray()
    {
        return function ($value) {
            return [$value];
        };
    }

    /**
     * @return \Closure
     */
    private function isNotHash()
    {
        return function ($value) {
            if (!is_array($value)) {
                return true;
            }

            if (array_values($value) === $value) {
                return true;
            }

            return false;
        };
    }

    /**
     * @param string $node
     *
     * @return \Closure
     */
    private function nodeRequiredIfEnabled($node)
    {
        return function ($value) use ($node) {
            if (!$value['enabled']) {
                return false;
            }

            if (isset($value[$node]) && !empty($value[$node])) {
                return false;
            }

            return true;
        };
    }

    /**
     * @param string $node
     * @param string $path
     *
     * @return string
     */
    private function nodeMustBeConfigured($node, $path)
    {
        return sprintf(
            'The child node "%s" at path "%s.%s" must be configured.',
            $node,
            $this->name,
            $path
        );
    }
}
