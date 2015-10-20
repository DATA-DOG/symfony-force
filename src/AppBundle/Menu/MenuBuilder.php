<?php

namespace AppBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

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
        $menu->addChild($this->trans('about'), ['route' => 'app_home_about', 'attributes' => [
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
            $dropdown->addChild($this->trans('profile'), ['route' => 'app_user_profile', 'attributes' => [
                'role' => 'presentation',
                'icon' => 'fa fa-child',
            ]]);
            // administration
            if ($user->hasRole('ROLE_ADMIN')) {
                $dropdown->addChild($this->trans('admin'), ['route' => 'admin', 'attributes' => [
                    'role' => 'presentation',
                    'icon' => 'fa fa-beer',
                ]]);
            }
            // logout
            $dropdown->addChild($this->trans('logout'), ['route' => 'app_user_logout', 'attributes' => [
                'role' => 'presentation',
                'icon' => 'fa fa-sign-out',
            ]]);
        }

        if (!$user instanceof UserInterface) {
            // signin
            $menu->addChild($this->trans('login'), ['route' => 'app_user_login', 'attributes' => [
                'role' => 'presentation',
                'icon' => 'fa fa-sign-in',
            ]]);
            // signup
            $menu->addChild($this->trans('sign_up'), ['route' => 'app_user_signup', 'attributes' => [
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

    /**
     * @param string $label
     * @return string
     */
    private function trans($label)
    {
        return $this->container->get('translator')->trans($label, [], 'menu');
    }
}
