<?php

namespace AppBundle\Behat\Page\User;

use AppBundle\Behat\AbstractPage;

class LoginPage extends AbstractPage
{
    public function route()
    {
        return 'app_user_login';
    }

    public function login($username, $password)
    {
        $this->session->getPage()->fillField('Username:', $username);
        $this->session->getPage()->fillField('Password:', $password);
        $this->session->getPage()->pressButton('Login');
    }
}
