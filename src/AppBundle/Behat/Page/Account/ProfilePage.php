<?php

namespace AppBundle\Behat\Page\Account;

use AppBundle\Behat\AbstractPage;

class ProfilePage extends AbstractPage
{
    protected function route()
    {
        return 'app_user_profile';
    }
}
