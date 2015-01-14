<?php

namespace spec\AppBundle\Entity;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UserSpec extends ObjectBehavior
{
    function it_should_implement_security_user_interface()
    {
        $this->shouldImplement('Symfony\Component\Security\Core\User\UserInterface');
    }

    function it_should_have_no_roles_by_default()
    {
        $this->getRoles()->shouldBe([]);
    }

    function it_should_be_able_to_add_defined_role()
    {
        $this->addRole('ROLE_ADMIN');
        $this->getRoles()->shouldBe(['ROLE_ADMIN']);
    }

    function it_should_skip_adding_undefined_role()
    {
        $this->addRole('ROLE_UNDEFINED');
        $this->getRoles()->shouldBe([]);
    }

    function it_should_be_able_to_remove_defined_role()
    {
        $this->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $this->removeRole('ROLE_ADMIN');

        $this->getRoles()->shouldBe(['ROLE_USER']);
    }

    function it_should_not_be_able_to_remove_undefined_role()
    {
        $this->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $this->removeRole('ROLE_UNDEFINED');

        $this->getRoles()->shouldBe(['ROLE_USER', 'ROLE_ADMIN']);
    }

    function it_should_add_user_role_when_confirmed()
    {
        $this->confirm();
        $this->getRoles()->shouldBe(['ROLE_USER']);
    }
}
