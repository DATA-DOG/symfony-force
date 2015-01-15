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
        $page = $this->getPage($page);
        return $page->open($this->get('router')->generate(
            $page->route(),
            $params ? $params->getRowsHash() : [],
            false
        ));
    }

    /**
     * @Given /^I should be on page "([^"]+)"$/
     */
    public function iShouldBeOnPage($page)
    {
        if (!$this->getPage($page)->isOpen()) {
            throw new \Exception("The page \"{$page}\" is not currently open..");
        }
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

                $page = new $class($this->getSession());
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
