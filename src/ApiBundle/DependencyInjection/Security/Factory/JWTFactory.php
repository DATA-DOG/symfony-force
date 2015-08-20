<?php

namespace ApiBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

class JWTFactory implements SecurityFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'security.authentication.provider.jwt.' . $id;
        $container
            ->setDefinition($providerId, new DefinitionDecorator('security.authentication.provider.jwt'))
            ->replaceArgument(0, new Reference($userProvider))
            ->replaceArgument(1, $config);

        $listenerId = 'security.authentication.listener.jwt.' . $id;
        $container
            ->setDefinition($listenerId, new DefinitionDecorator('security.authentication.listener.jwt'));

        return [$providerId, $listenerId, $defaultEntryPoint];
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return 'pre_auth';
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'jwt';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(NodeDefinition $node)
    {
        $node
            ->children()
                ->scalarNode('pub_key')->defaultValue('')->end()
            ->end();
    }
}
