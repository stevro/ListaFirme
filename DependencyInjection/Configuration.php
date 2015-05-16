<?php

namespace Stev\ListaFirmeBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('stev_lista_firme');

        $rootNode
                ->children()
                    ->scalarNode('username')
                        ->cannotBeEmpty()
                        ->isRequired()
                    ->end()
                    ->scalarNode('password')
                        ->cannotBeEmpty()
                        ->isRequired()
                    ->end()
                    ->scalarNode('offline')
                        ->defaultValue(false)
                        ->isOptional()
                    ->end()
                ->end();

        return $treeBuilder;
    }
}
