<?php

namespace AppBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class MenuBuilder extends ContainerAware
{
    /**
     * @param FactoryInterface $factory
     * @return \Knp\Menu\ItemInterface
     */
    public function top(FactoryInterface $factory)
    {
        $menu = $factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav navbar-nav pull-right');

        $child = function($label, $route) use($menu) {
            $attributes = ['role' => 'presentation'];
            return $menu->addChild($label, compact('route', 'attributes'));
        };

        $user = $this->getUser();
        if ($user instanceof UserInterface) {
            $child($user, 'app_user_profile');
            if ($user->hasRole('ROLE_ADMIN')) {
                $child('Administration', 'admin')->setLinkAttribute('class', 'text-danger');
            }
            $child('Logout', 'app_user_logout');
        } else {
            $child('Login', 'app_user_login');
            $child('Sign-up', 'app_user_signup');
        }

        return $menu;
    }

    /**
     * @return UserInterface
     */
    private function getUser()
    {
        if (!$this->container->has('security.token_storage')) {
            throw new \LogicException('The SecurityBundle is not registered in your application.');
        }

        $token = $this->container->get('security.token_storage')->getToken();
        if (!$token instanceof TokenInterface) {
            return null;
        }

        return $token->getUser();
    }
}
