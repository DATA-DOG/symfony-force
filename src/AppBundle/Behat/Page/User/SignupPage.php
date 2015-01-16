<?php

namespace AppBundle\Behat\Page\User;

use AppBundle\Behat\AbstractPage;

class SignupPage extends AbstractPage
{
    protected function route()
    {
        return 'app_user_signup';
    }

    public function signup($email)
    {
        $this->session->getPage()->fillField('Email:', $email);
        $this->session->getPage()->pressButton('Signup');
    }
}
