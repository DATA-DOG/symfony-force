<?php

namespace AppBundle\Controller;

trait DoctrineController
{
    protected function persist(...$entities)
    {
        foreach ($entities as $entity) {
            $this->getDoctrine()->getManager()->persist($entity);
        }
    }

    protected function remove(...$entities)
    {
        foreach ($entities as $entity) {
            $this->getDoctrine()->getManager()->remove($entity);
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
