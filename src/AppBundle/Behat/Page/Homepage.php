<?php

namespace AppBundle\Behat\Page;

use AppBundle\Behat\AbstractPage;

class Homepage extends AbstractPage
{
    protected function route()
    {
        return 'homepage';
    }

    public function mustShowFullnameOnProfileLink($fullname)
    {
        if (!$link = $this->xpath("//a[contains(@href, \"/profile\")]")) {
            throw new \RuntimeException("Could not find profile link anywhere on page..");
        }

        if ($link->getText() !== $fullname) {
            throw new \RuntimeException("The profile link \"{$link->getText()}\" does not match expected \"{$fullname}\".");
        }
    }
}
