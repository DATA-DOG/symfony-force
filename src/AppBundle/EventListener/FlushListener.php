<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Doctrine\ORM\EntityManager;

class FlushListener
{
    private $em;
    private $sub;

    public function __construct(EntityManager $em, FlushSubscriber $sub)
    {
        $this->em = $em;
        $this->sub = $sub;
    }

    public function onEarlyKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        // reset flushed state on each request, since kernel may not be rebooted
        $this->sub->flushed = false;
        $this->sub->inRequest = true;
    }

    public function onLateKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        
        $this->sub->inRequest = false;
    }
}
