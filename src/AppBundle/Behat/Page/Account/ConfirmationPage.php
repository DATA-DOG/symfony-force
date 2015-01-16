<?php

namespace AppBundle\Behat\Page\Account;

use AppBundle\Behat\AbstractPage;

class ConfirmationPage extends AbstractPage
{
    protected function route()
    {
        return 'app_user_confirm';
    }

    public function confirmWithPersonalDetails($fullname)
    {
        list ($firstname, $lastname) = explode(' ', $fullname);

        $this->fillField('Firstname:', $firstname);
        $this->fillField('Lastname:', $lastname);
        $this->fillField('Password:', 'S3cretpassword');
        $this->fillField('Repeat password:', 'S3cretpassword');
        $this->pressButton('Confirm');
    }
}
