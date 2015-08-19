<?php

namespace AppBundle\Behat;

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Symfony\Component\HttpKernel\KernelInterface;
use UnexpectedValueException;

class BaseContext extends RawMinkContext implements KernelAwareContext
{
    /**
     * @var KernelInterface
     */
    protected $kernel;

    protected $placeholders;

    protected $mink;

    /**
     * @BeforeScenario
     */
    public function linkUsedSubcontexts(BeforeScenarioScope $scope)
    {
        $this->placeholders = $scope->getEnvironment()->getContext('AppBundle\Behat\PlaceholderContext');
        if ($scope->getEnvironment()->hasContextClass('Behat\MinkExtension\Context\MinkContext')) {
            $this->mink = $scope->getEnvironment()->getContext('Behat\MinkExtension\Context\MinkContext');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Returns Container instance.
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return $this->kernel->getContainer();
    }

    /**
     * Get service by id.
     *
     * @param string $id
     *
     * @return object
     */
    protected function get($id)
    {
        return $this->getContainer()->get($id);
    }

    protected function getParameter($name)
    {
        return $this->getContainer()->getParameter($name);
    }

    /**
     * @param string $name
     */
    protected function repo($name)
    {
        return $this->get('em')->getRepository($name);
    }

    /**
     * @param string $type
     * @param string $path
     */
    protected function find($type, $path)
    {
        return $this->getSession()->getPage()->find($type, $path);
    }

    /**
     * @param string $route
     */
    protected function visit($route, array $params = [])
    {
        $params = array_merge(['_locale' => 'en'], $params);
        $path = $this->get('router')->generate($route, $params, true);
        return $this->visitPath($path);
    }

    /**
     * @param boolean $val
     */
    protected function true($val, $msg = '')
    {
        if ($val !== true) {
            throw new UnexpectedValueException($msg ?: "Expected true, but got false");
        }
    }

    protected function null($val, $msg = '')
    {
        if ($val !== null) {
            throw new UnexpectedValueException($msg ?: "Expected null, but got some value");
        }
    }

    protected function notNull($val, $msg = '')
    {
        if ($val === null) {
            throw new UnexpectedValueException($msg ?: "Expected some value, but got null");
        }
    }

    /**
     * @param integer $expected
     * @param integer $actual
     */
    protected function same($expected, $actual, $msg = '')
    {
        if ($expected !== $actual) {
            throw new UnexpectedValueException($msg ?: "Expected value '{$expected}' is not the same as '{$actual}'");
        }
    }

    protected function false($val, $msg = '')
    {
        if ($val !== false) {
            throw new UnexpectedValueException($msg ?: "Expected false, but got true");
        }
    }

    protected function like($expected, $actual, $msg = '')
    {
        if ($expected != $actual) {
            throw new UnexpectedValueException($msg ?: "Expected value '{$expected}' is not like '{$actual}'");
        }
    }

    protected function sameOrMatches($expectation, $actual, $msg = '')
    {
        if ($expectation !== $actual && !@preg_match($expectation, $actual)) {
            throw new UnexpectedValueException($msg ?: "Expected value '{$expectation}' is not the same, or does not match actual '{$actual}'");
        }
    }

    protected function likeOrMatches($expectation, $actual, $msg = '')
    {
        if ($expectation != $actual && !@preg_match($expectation, $actual)) {
            throw new UnexpectedValueException($msg ?: "Expected value '{$expectation}' is not like, or does not match actual '{$actual}'");
        }
    }
}
