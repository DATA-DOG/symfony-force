<?php

namespace ApiBundle;

use ApiBundle\DependencyInjection\Security\Factory\JWTFactory;
use ApiBundle\DependencyInjection\Security\Factory\JWTAuthFactory;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ApiBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new JWTFactory());
        $extension->addSecurityListenerFactory(new JWTAuthFactory());
    }
}
