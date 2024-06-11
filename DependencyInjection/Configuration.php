<?php

namespace Stev\ListaFirmeBundle\DependencyInjection;

use Stev\ListaFirmeBundle\Lib\CIFChecker;
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
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('stev_lista_firme');
        $rootNode = $treeBuilder->getRootNode();
        
        $supportedCheckers = array(CIFChecker::CHECKER_LISTA_FIRME, CIFChecker::CHECKER_OPEN_API, CIFChecker::CHECKER_ANAF, CIFChecker::CHECKER_VIES);
        
        $rootNode
                ->children()
                    ->scalarNode('cifChecker')
                        ->validate()
                            ->ifNotInArray($supportedCheckers)
                            ->thenInvalid('The cifChecker %s is not supported. Please choose one of '.json_encode($supportedCheckers))
                        ->end()
                        ->cannotBeOverwritten()
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('username')
                        ->defaultValue('demo')
                    ->end()
                    ->scalarNode('password')
                        ->defaultValue('demo')
                    ->end()
                    ->scalarNode('offline')
                        ->defaultFalse()
                    ->end()
                    ->scalarNode('enabled')
                        ->defaultTrue()
                    ->end()
                    ->scalarNode('pathToPhantom')
                    ->end()
                    ->scalarNode('apiKey')
                    ->end()
                ->end();

        return $treeBuilder;
    }
}
