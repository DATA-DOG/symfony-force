<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;

class Controller extends BaseController
{
    protected function flash($section, $msg, array $params = [], $domain = 'messages')
    {
        $this->get('session')->getFlashBag()->add($section, $this->get('translator')->trans(
            $msg, $params, $domain
        ));
    }

    protected function persist(...$entities)
    {
        foreach ($entities as $entity) {
            $this->getDoctrine()->getManager()->persist($entity);
        }
    }

    protected function flush($class = null)
    {
        $this->getDoctrine()->getManager()->flush($class);
    }

    protected function repo($class)
    {
        return $this->getDoctrine()->getManager()->getRepository($class);
    }
}
