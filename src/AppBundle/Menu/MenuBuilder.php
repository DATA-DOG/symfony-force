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

        // about
        $menu->addChild('About', ['route' => 'app_home_about', 'attributes' => [
            'role' => 'presentation',
            'icon' => 'fa fa-book',
        ]]);
        $user = $this->getUser();

        if ($user instanceof UserInterface) {
            // dropdown
            $dropdown = $menu->addChild($user, ['attributes' => [
                'role' => 'presentation',
                'dropdown' => true,
                'icon' => 'fa fa-user',
            ]]);
            // profile
            $dropdown->addChild('Profile', ['route' => 'app_user_profile', 'attributes' => [
                'role' => 'presentation',
                'icon' => 'fa fa-child',
            ]]);
            // administration
            if ($user->hasRole('ROLE_ADMIN')) {
                $dropdown->addChild('Admin', ['route' => 'admin', 'attributes' => [
                    'role' => 'presentation',
                    'icon' => 'fa fa-beer',
                ]]);
            }
            // logout
            $dropdown->addChild('Logout', ['route' => 'app_user_logout', 'attributes' => [
                'role' => 'presentation',
                'icon' => 'fa fa-sign-out',
            ]]);
        }

        if (!$user instanceof UserInterface) {
            // signin
            $menu->addChild('Login', ['route' => 'app_user_login', 'attributes' => [
                'role' => 'presentation',
                'icon' => 'fa fa-sign-in',
            ]]);
            // signup
            $menu->addChild('Sign-up', ['route' => 'app_user_signup', 'attributes' => [
                'role' => 'presentation',
                'icon' => 'fa fa-user-plus',
            ]]);
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
