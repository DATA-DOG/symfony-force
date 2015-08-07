<?php

namespace AppBundle\Behat;

trait IsLayoutAware
{
    /**
     * @Then /^I should see (error|danger|success|info|notice) notification "([^"]+)"$/
     */
    function iShouldSeeNotification($type, $text)
    {
        switch ($type) {
        case 'error':
            $type = 'danger';
            break;
        case 'notice':
            $type = 'info';
            break;
        }

        $q = '//div[contains(@class, "alert") and contains(@class, "alert-' . $type . '") and contains(., "' . $text . '")]';
        if (null === $this->getSession()->getPage()->find('xpath', $q)) {
            throw new \RuntimeException("Notification of type '$type' with message '$text' was not found on page");
        }
    }

    /**
     * @Then /^I should see a form field error "([^"]+)"$/
     */
    function iShouldSeeAFormFieldError($text)
    {
        $q = '//div[contains(@class, "has-error")]//span[contains(@class, "help-block") and contains(., "' . $text . '")]';
        if (null === $this->getSession()->getPage()->find('xpath', $q)) {
            throw new \RuntimeException("Form field error '$text' was not found on page");
        }
    }
}
