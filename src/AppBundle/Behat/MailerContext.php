<?php

namespace AppBundle\Behat;

class MailerContext extends BaseContext
{
    /**
     * @BeforeScenario
     */
    public function cleanEmails()
    {
        $this->get('mailer')->getTransport()->getSpool()->messages = [];
    }

     /**
     * @Then /^I should receive an email to "([^"]*)"$/
     */
    function iShouldHaveReceivedAnEmailTo($email)
    {
        foreach ($this->get('mailer')->getTransport()->getSpool()->messages as $message) {
            foreach ($message->getTo() as $address => $name) {
                if ($address === $email) {
                    return;
                }
            }
        }
        throw new \Exception("An email to '$email' was never sent");
    }

    /**
     * @When /^I follow the confirmation link in my email$/
     */
    function iFollowTheConfirmationLinkInMyEmail()
    {
        foreach ($this->get('mailer')->getTransport()->getSpool()->messages as $message) {
            if (preg_match('/href="([^"]+)/smi', $message->getBody(), $m)) {
                return $this->getSession()->visit($m[1]);
            }
        }

        throw new \Exception("A confirmation link was not found in any email");
    }
}
