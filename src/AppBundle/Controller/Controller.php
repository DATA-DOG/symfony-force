<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;

class Controller extends BaseController
{
    protected function flash($section, $msg, array $params = [], $domain = 'flashes')
    {
        $this->get('session')->getFlashBag()->add($section, $this->get('translator')->trans(
            $msg, $params, $domain
        ));
    }
}
