<?php

namespace AppBundle\Behat;

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use AppBundle\Entity\User;

class UserContext extends RawMinkContext implements KernelAwareContext
{
    use IsNavigationAware;
    use IsLayoutAware;

    /**
     * @BeforeScenario
     */
    function resetSecurityContext()
    {
        $this->get('security.context')->setToken(null);
    }

    /**
     * @Given /^(confirmed|unconfirmed) user named "([^"]+)"$/
     */
    function userNamed($type, $name)
    {
        $names = explode(' ', $name);

        $em = $this->get('em');
        $user = new User();
        $user->setFirstname($names[0]);
        $user->setLastname(isset($names[1]) ? $names[1] : '');
        $user->setEmail(strtolower(implode('.', $names)) . '@datadog.lt');
        $user->setRoles(['ROLE_USER']);

        if ('unconfirmed' === $type) {
            $user->setConfirmationToken("secret-token");
        } else {
            $encoder = $this->get('security.encoder_factory')->getEncoder($user);
            $user->setPassword($encoder->encodePassword('S3cretpassword', $user->getSalt()));
        }
        $em->persist($user);
        $em->flush();
    }

    /**
     * @Given /^I'm logged in as "([^"]*)"$/
     * @When /^I login as "([^"]*)" using password "([^"]*)"$/
     * @When /^I try to login as "([^"]*)" using password "([^"]*)"$/
     */
    function iTryToLoginAsUsingPassword($email, $password = 'S3cretpassword')
    {
        $this->getPage('User Login')->mustBeOpen()->login($email, $password);

        $user = $this->get('em')->getRepository('AppBundle:User')->findOneByEmail($email);
        if ($user) {
            $encoder = $this->get('security.encoder_factory')->getEncoder($user);
            $password = $encoder->encodePassword($password, $user->getSalt());
            if ($password === $user->getPassword()) {
                $this->setCurrentUser($user);
            }
        }
    }

    /**
     * @Then /^I should be logged in$/
     */
    function iShouldBeLoggedIn()
    {
        $this->getPage('Homepage')
            ->mustBeOpen()
            ->mustShowFullnameOnProfileLink((string)$this->mustGetUser());
    }

    private function setCurrentUser(User $user)
    {
        $token = new UsernamePasswordToken($user, $user->getPassword(), "main", $user->getRoles());
        $this->get('security.context')->setToken($token);
    }
}
