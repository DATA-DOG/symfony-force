<?php

namespace AppBundle\Behat;

class PageContext extends BaseContext
{
    /**
     * @Given /^I am on "([^"]+)" page$/
     * @When /^I visit "([^"]+)" page$/
     */
    function iAmOnLandingPage($name)
    {
        switch ($name) {
        case "landing":
            return $this->visit("homepage");
        case "login":
            return $this->visit("app_user_login");
        case "signup":
            return $this->visit("app_user_signup");
        case "profile":
            return $this->visit("app_user_profile");
        case "admin":
            return $this->visit("admin_dashboard_index");
        default:
            throw new \InvalidArgumentException("Page: {$name} route is not defined yet.");
        }
    }

    /**
     * @Then /^I should see "([^"]+)" on page headline$/
     * @Then /^I should see "([^"]+)" in page headline$/
     */
    function iShouldSeeTextOnPageHeadline($text)
    {
        $this->notNull(
            $this->find('xpath', '//h1[contains(., "' . $text . '")] | //h2[contains(., "' . $text . '")] | //h3[contains(., "'.$text.'")]'),
            "Text '$text' was not found on page headline"
        );
    }

    /**
     * @Then /^the response code should be (\d+)$/
     */
    function theResponseCodeShouldBe($code)
    {
        $this->same(intval($code), $actual = $this->getSession()->getStatusCode(), "Invalid response code, expected $code, got $actual");
    }

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
        $this->notNull($this->find('xpath', $q), "Notification of type '$type' with message '$text' was not found on page");
    }

    /**
     * @Then /^I should see a form field error "([^"]+)"$/
     */
    function iShouldSeeAFormFieldError($text)
    {
        $q = '//div[contains(@class, "has-error")]//span[contains(@class, "help-block") and contains(., "' . $text . '")]';
        $this->notNull($this->find('xpath', $q), "Form field error '$text' was not found on page");
    }
}
