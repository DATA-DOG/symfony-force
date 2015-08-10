<?php

namespace AppBundle\Behat;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class MailerContext extends BaseContext
{
    /**
     * @BeforeScenario
     */
    public function cleanEmails()
    {
        $emailDir = $this->getParameter('kernel.root_dir') . '/spool-test';
        if (is_dir($emailDir)) {
            $fs = new Filesystem();
            $fs->remove($emailDir);
        }
    }

     /**
     * @Then /^I should receive an email to "([^"]*)"$/
     */
    function iShouldHaveReceivedAnEmailTo($email)
    {
        $emailDir = $this->getParameter('kernel.root_dir') . '/spool-test';
        if (!is_dir($emailDir)) {
            throw new \Exception("There were no emails sent");
        }
        $finder = new Finder();
        $finder->files()->in($emailDir);
        foreach ($finder as $file) {
            $message = unserialize(file_get_contents($file->getRealpath()));
            foreach ($message->getTo() as $address => $name) {
                if ($address === $email) {
                    return;
                    // next check the body
                    if (strpos($message->getBody(), $body->getRaw()) !== false) {
                        return; // found email
                    }
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
        $emailDir = $this->getParameter('kernel.root_dir') . '/spool-test';
        if (!is_dir($emailDir)) {
            throw new \Exception("There were no emails sent");
        }
        $finder = new Finder();
        $finder->files()->in($emailDir);
        foreach ($finder as $file) {
            $message = unserialize(file_get_contents($file->getRealpath()));
            if (preg_match('/href="([^"]+)/smi', $message->getBody(), $m)) {
                return $this->getSession()->visit($m[1]);
            }
        }

        throw new \Exception("A confirmation link was not found in any email");
    }
}
