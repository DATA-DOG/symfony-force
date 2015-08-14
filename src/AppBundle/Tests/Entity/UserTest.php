<?php

namespace AppBundle\Tests\Entity;

use AppBundle\Entity\User;

class UserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function should_have_no_roles_by_default()
    {
        $user = new User;
        $this->assertSame([], $user->getRoles());
    }

    /**
     * @test
     */
    public function should_be_able_to_add_defined_role()
    {
        $user = new User;
        $user->addRole('ROLE_ADMIN');
        $this->assertSame($user->getRoles(), ['ROLE_ADMIN']);
    }

    /**
     * @test
     */
    public function should_skip_adding_undefined_role()
    {
        $user = new User;
        $user->addRole('ROLE_UNDEFINED');
        $this->assertSame([], $user->getRoles());
    }

    /**
     * @test
     */
    public function should_be_able_to_remove_defined_role()
    {
        $user = new User;
        $user->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $user->removeRole('ROLE_ADMIN');

        $this->assertSame(['ROLE_USER'], $user->getRoles());
    }

    /**
     * @test
     */
    public function should_not_be_able_to_remove_undefined_role()
    {
        $user = new User;
        $user->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $user->removeRole('ROLE_UNDEFINED');

        $this->assertSame(['ROLE_USER', 'ROLE_ADMIN'], $user->getRoles());
    }

    /**
     * @test
     */
    public function should_add_user_role_when_confirmed()
    {
        $user = new User;
        $user->confirm();
        $this->assertSame(['ROLE_USER'], $user->getRoles());
    }
}
