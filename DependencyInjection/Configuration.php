<?php

namespace Fgms\SpecialOffersBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('fgms_special_offers');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        $rootNode
            ->children()
                ->scalarNode('api_key')
                    ->isRequired()
                ->end()
                ->scalarNode('secret')
                    ->isRequired()
                ->end()
                ->integerNode('expired')
                    ->min(1)
                    ->defaultValue(10)
                ->end()
                ->arrayNode('notifications')
                    ->canBeEnabled()
                    ->children()
                        ->arrayNode('from')
                            ->isRequired()
                            ->children()
                                ->scalarNode('name')
                                    ->defaultValue(null)
                                ->end()
                                ->scalarNode('address')
                                    ->isRequired()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('to')
                            ->isRequired()
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('name')
                                        ->defaultValue(null)
                                    ->end()
                                    ->scalarNode('address')
                                        ->isRequired()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->scalarNode('start_template')
                            ->isRequired()
                        ->end()
                        ->scalarNode('end_template')
                            ->isRequired()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
