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
class StevListaFirmeExtension extends Extension {

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container) {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        if ($config['cifChecker'] == \Stev\ListaFirmeBundle\Lib\CIFChecker::CHECKER_LISTA_FIRME) {
            if (!isset($config['username']) || $config['password']) {
                throw new \RuntimeException('Username and password are mandatory for listaFirme checker');
            }
        } elseif ($config['cifChecker'] == \Stev\ListaFirmeBundle\Lib\CIFChecker::CHECKER_MFIN) {
            if (!isset($config['pathToPhantom'])) {
                $config['pathToPhantom'] = $container->getParameter('kernel.root_dir') . '/../bin/phantomjs';
            }
        }

        $container->setParameter('stev_lista_firme.cifChecker', $config['cifChecker']);
        $container->setParameter('stev_lista_firme.username', $config['username']);
        $container->setParameter('stev_lista_firme.password', $config['password']);
        $container->setParameter('stev_lista_firme.offline', $config['offline']);
        $container->setParameter('stev_lista_firme.enabled', $config['enabled']);
        $container->setParameter('stev_lista_firme.pathToPhantom', $config['pathToPhantom']);

        $loader->load('services.yml');
    }

}
