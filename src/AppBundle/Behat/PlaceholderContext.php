<?php

namespace AppBundle\Behat;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Testwork\Tester\Result\TestResult;

class PlaceholderContext implements Context
{
    private $placeholders = [];

    /**
     * @BeforeScenario
     */
    function clearAllPlaceholders()
    {
        $this->placeholders = [];
    }

    /**
     * @AfterStep
     */
    function showRecentPlaceholders(AfterStepScope $scope)
    {
        if ($scope->getTestResult()->getResultCode() == TestResult::FAILED) {
            if ($this->placeholders) {
                echo "\nPlaceholders:";
                foreach ($this->placeholders as $name => $value) {
                    echo sprintf("\n    %s: %s", $name, $value);
                }
                echo "\n\n";
            }
        }
    }

    public function all()
    {
        return $this->placeholders;
    }

    public function get($name)
    {
        if (array_key_exists($name, $this->placeholders)) {
            throw new \Exception("The placeholder: \"{$name}\" was not set..");
        }
        return $this->placeholders[$name];
    }

    public function set($name, $value)
    {
        if (array_key_exists($name, $this->placeholders)) {
            throw new \Exception("The placeholder: \"{$name}\" was already set..");
        }
        $this->placeholders[$name] = (string) $value;
    }

    public function replace($text)
    {
        return preg_replace_callback("#%([^%]+)%#", function($m) {
            return isset($this->placeholders[$m[1]]) ? $this->placeholders[$m[1]] : $m[0];
        }, $text);
    }
}
