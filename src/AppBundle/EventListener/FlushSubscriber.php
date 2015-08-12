<?php

namespace AppBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PreFlushEventArgs;

class FlushSubscriber implements EventSubscriber
{
    public $flushed;
    public $inRequest = false;

    public function preFlush(PreFlushEventArgs $args)
    {
        if (!$this->inRequest) {
            // let console commands handle flushes anyway they want
            return;
        }

        $em = $args->getEntityManager();
        if ($em->getConnection()->isTransactionActive()) {
            // the transaction is managed manually and was already started
            // probably it won't be handled since it is the end of response
            // but anyways, it won't cause trouble
            return;
        }

        if ($this->flushed) {
            throw new \BadMethodCallException("The flush can be run only once and is run automatically in the end of each request to prevent data inconsistencies and bad design.");
        }

        $this->flushed = true;
    }

    public function getSubscribedEvents()
    {
        return [Events::preFlush];
    }
}
