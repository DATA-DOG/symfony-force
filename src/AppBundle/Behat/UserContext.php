<?php

namespace AppBundle\Behat;

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use AppBundle\Entity\User;

class UserContext extends RawMinkContext implements KernelAwareContext
{
    use IsNavigationAware;
    use IsLayoutAware;

    /**
     * @Given /^(confirmed|unconfirmed) user named "([^"]+)"$/
     */
    public function userNamed($type, $name)
    {
        $em = $this->get('em');
        $user = new User();
        $user->setFirstname($name);
        $user->setLastname('');
        $user->setEmail(strtolower($name) . '@datadog.lt');
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
     * @When /^I try to login as "([^"]*)" using password "([^"]*)"$/
     */
    public function iTryToLoginAsUsingPassword($username, $password)
    {
        $page = $this->getPage('User Login');
        if (!$page->isOpen()) {
            throw new \Exception("In order to login you must be on login page.");
        }
        $page->login($username, $password);
    }
}
