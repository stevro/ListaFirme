<?php

namespace Stev\ListaFirmeBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class StevListaFirmeExtension extends Extension
{

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        switch ($config['cifChecker']) {
            case \Stev\ListaFirmeBundle\Lib\CIFChecker::CHECKER_LISTA_FIRME:
                if (!isset($config['username']) || $config['password']) {
                    throw new \RuntimeException('Username and password are mandatory for listaFirme checker');
                }
                break;
            case \Stev\ListaFirmeBundle\Lib\CIFChecker::CHECKER_OPEN_API:
                if (!isset($config['apiKey'])) {
                    throw new \RuntimeException('ApiKey is mandatory for OpenAPI checker since 15.09.2016');
                }
                break;
            default:
                break;
        }

        $container->setParameter('stev_lista_firme.cifChecker', $config['cifChecker']);
        $container->setParameter('stev_lista_firme.username', $config['username']);
        $container->setParameter('stev_lista_firme.password', $config['password']);
        $container->setParameter('stev_lista_firme.offline', $config['offline']);
        $container->setParameter('stev_lista_firme.enabled', $config['enabled']);
        $container->setParameter('stev_lista_firme.pathToPhantom', isset($config['pathToPhantom']) ? $config['pathToPhantom'] : null );
        $container->setParameter('stev_lista_firme.apiKey', $config['apiKey']);

        $loader->load('services.yml');
    }

}
