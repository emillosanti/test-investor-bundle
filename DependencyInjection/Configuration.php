<?php

namespace SAM\InvestorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sam_investor');

        $rootNode
            ->children()
                ->arrayNode('investment')
                    ->children()
                        ->scalarNode('min')
                            ->defaultValue(0)
                        ->end()
                        ->scalarNode('max')
                            ->defaultValue(30000)
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('analytics')
                    ->children()
                        ->arrayNode('investment_amount_range_points')
                        ->scalarPrototype()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
