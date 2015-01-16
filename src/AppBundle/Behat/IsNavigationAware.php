<?php

namespace AppBundle\Behat;

/**
 * Must implement KernelAwareContext
 * and must extend RawMinkContext
 */
trait IsNavigationAware
{
    use IsKernelAware;

    /**
     * @Given /^I am on page "([^"]+)"$/
     * @Given /^I am on page "([^"]+)" with params:$/
     * @When /^I visit page "([^"]+)"$/
     * @When /^I visit page "([^"]+)" with params:$/
     */
    public function iVisitPageWithParams($page, \Behat\Gherkin\Node\TableNode $params = null)
    {
        return $this->getPage($page)->open($params ? $params->getRowsHash() : []);
    }

    /**
     * @Then /^I should be on page "([^"]+)"$/
     * @Then /^I should be on page "([^"]+)" with params:$/
     */
    public function iShouldBeOnPage($page, \Behat\Gherkin\Node\TableNode $params = null)
    {
        $this->getPage($page)->mustBeOpen($params ? $params->getRowsHash() : []);
    }

    private function getPage($name)
    {
        static $pages = [];
        if (isset($pages[$name])) {
            return $pages[$name];
        }

        $className = implode('\\', array_map('ucfirst', explode(' ', $name)));
        $guessed = [];
        foreach ($this->kernel->getBundles() as $bundle) {
            $class = $bundle->getNamespace() . '\\Behat\\Page\\' . $className;
            if (!class_exists($class)) {
                $guessed[] = $class;
                $class .= 'Page';
            }
            if (class_exists($class)) {
                if (!is_subclass_of($class, 'AppBundle\\Behat\\AbstractPage')) {
                    throw new \RuntimeException("Found page \"{$name}\" class \"{$class}\" but it does not extend Page abstract class.");
                }

                $page = new $class($this->getSession(), $this->get('router'));
                if ($page instanceof ContainerAwareInterface) {
                    $page->setContainer($this->container);
                }
                $pages[$name] = $page;
                return $page;
            }
            $guessed[] = $class;
        }

        throw new \RuntimeException("Could not find page by \"{$name}\" name, tried classes: \"" . implode('" ', $guessed) . '"..');
    }
}
