<?php

namespace AppBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class AppExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('security.yml');
        $loader->load('twig.yml');
        $loader->load('mailer.yml');
        $loader->load('menu.yml');
        $loader->load('listeners/doctrine.yml');
        $loader->load('listeners/kernel.yml');

        if (in_array($container->getParameter('kernel.environment'), ['prod', 'test'], true)) {
            $loader->load('cache_prod.yml');
        } else {
            $loader->load('cache.yml');
        }
    }
}
