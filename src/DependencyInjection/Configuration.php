<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('whatwedo_search');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
            ->arrayNode('chains')
            ->useAttributeAsKey('name')
            ->arrayPrototype()
            ->children()
            ->arrayNode('filters')
            ->useAttributeAsKey('name')
            ->arrayPrototype()
            ->children()
            ->scalarNode('class')
            ->isRequired()
            ->cannotBeEmpty()
            ->end()
            ->arrayNode('options')
            ->scalarPrototype()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end();

        $rootNode
            ->children()
            ->arrayNode('entities')
            ->prototype('array')
            ->children()
            ->scalarNode('class')
            ->isRequired()
            ->cannotBeEmpty()
            ->end()
            ->end()
            ->children()
            ->arrayNode('fields')
            ->prototype('array')
            ->children()
            ->scalarNode('name')
            ->isRequired()
            ->cannotBeEmpty()
            ->end()
            ->end()
            ->children()
            ->scalarNode('formatter')
            ->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
