<?php

namespace AppBundle\Behat\Page;

use AppBundle\Behat\AbstractPage;

class Homepage extends AbstractPage
{
    public function route()
    {
        return 'homepage';
    }
}
