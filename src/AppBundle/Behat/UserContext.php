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
        list ($firstname, $lastname) = $names;

        $em = $this->get('em');
        $user = new User();
        if ('confirmed' === $type) {
            $user->setFirstname($firstname);
            $user->setLastname($lastname);
        }
        $user->setEmail(strtolower(implode('.', $names)) . '@datadog.lt');
        $user->setRoles(['ROLE_USER']);

        if ('unconfirmed' === $type) {
            $user->setConfirmationToken(implode('-', array_map('strtolower', $names)) . '-token');
        } else {
            $encoder = $this->get('security.encoder_factory')->getEncoder($user);
            $user->setPassword($encoder->encodePassword('S3cretpassword', $user->getSalt()));
        }
        $em->persist($user);
        $em->flush();
        return $user;
    }

    /**
     * @When /^I confirm my account "([^"]+)" with personal details$/
     */
    function iAmConfirmingMyAccount($name)
    {
        $user = $this->userNamed('unconfirmed', $name);
        $page = $this->iFollowConfirmationLinkFromMyEmail($user->getEmail());
        $page->confirmWithPersonalDetails($name);
    }

    /**
     * @Given /^I'm logged in as "([^"]*)"$/
     * @When /^I login as "([^"]*)" using password "([^"]*)"$/
     * @When /^I try to login as "([^"]*)" using password "([^"]*)"$/
     */
    function iTryToLoginAsUsingPassword($email, $password = 'S3cretpassword')
    {
        $this->getPage('User Login')->open()->login($email, $password);

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
     * @When /^I attempt to signup as "([^"]*)"$/
     * @When /^I signup as "([^"]*)"$/
     */
    function iSignupAs($email)
    {
        return $this->getPage('User Signup')->mustBeOpen()->signup($email);
    }

    /**
     * @When /^I follow confirmation link from my "([^"]*)" email$/
     */
    function iFollowConfirmationLinkFromMyEmail($email)
    {
        $token = implode('-', explode('.', substr($email, 0, strpos($email, '@')))) . '-token';
        return $this->getPage('Account Confirmation')->open(compact('token'));
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
