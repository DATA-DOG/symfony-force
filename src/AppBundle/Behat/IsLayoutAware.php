<?php

namespace AppBundle\Behat;

trait IsLayoutAware
{
    /**
     * @Then /^I should see (danger|success|info) notification "([^"]+)"$/
     */
    function iShouldSeeNotification($type, $text)
    {
        $q = '//div[contains(@class, "alert") and contains(@class, "alert-' . $type . '") and contains(., "' . $text . '")]';
        if (null === $this->getSession()->getPage()->find('xpath', $q)) {
            throw new \Exception("Notification of type '$type' with message '$text' was not found on page");
        }
    }
}
