<?php

namespace AppBundle;

use Knp\Menu\FactoryInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class Menu
{
    private $factory;
    private $translator;

    public function __construct(FactoryInterface $factory, TranslatorInterface $translator)
    {
        $this->factory = $factory;
        $this->translator = $translator;
    }

    public function top(SecurityContextInterface $context)
    {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav navbar-nav pull-right');

        $child = function($label, $route) use($menu) {
            $attributes = ['role' => 'presentation'];
            $menu->addChild($label, compact('route', 'attributes'));
        };
        if ($context->isGranted('ROLE_USER')) {
            $child((string)$context->getToken()->getUser(), 'homepage');
            $child('Logout', 'app_user_logout');
        } else {
            $child('Login', 'app_user_login');
            $child('Register', 'app_user_register');
        }

        return $menu;
    }
}
