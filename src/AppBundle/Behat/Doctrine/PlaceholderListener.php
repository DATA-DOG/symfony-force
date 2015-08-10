<?php

namespace AppBundle\Behat\Doctrine;

use AppBundle\Behat\PlaceholderContext;
use AppBundle\Entity;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;

class PlaceholderListener implements EventSubscriber
{
    private $placeholders;

    public function __construct(PlaceholderContext $placeholders)
    {
        $this->placeholders = $placeholders;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        $entity = $args->getEntity();
        $meta = $em->getClassMetadata(get_class($entity));

        $id = current(array_values($meta->getIdentifierValues($entity)));
        $this->placeholders->set($this->label($entity), $id);
    }

    public function getSubscribedEvents()
    {
        return [Events::postPersist];
    }

    private function label($entity)
    {
        switch (true) {
        case $entity instanceof Entity\User:
            return $entity->getEmail();
        case $entity instanceof Entity\MailTemplate:
            return $entity->getAlias();
        case method_exists($entity, 'getName'):
            return $entity->getName();
        case method_exists($entity, 'getTitle'):
            return $entity->getTitle();
        case method_exists($entity, 'getLabel'):
            return $entity->getTitle();
        case method_exists($entity, '__toString'):
            return (string)$entity;
        default:
            return spl_object_hash($entity).'-'.get_class($entity);
        }
    }
}
