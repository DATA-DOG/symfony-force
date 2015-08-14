<?php

namespace AppBundle\Menu;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Voter\VoterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class RequestVoter implements VoterInterface
{
    private $request;

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        $this->request = $event->getRequest();
    }

    public function matchItem(ItemInterface $item)
    {
        if (null === $this->request) {
            return null;
        }

        if ($item->getUri() === $this->request->getRequestUri()) {
            // URL's completely match
            return true;
        }

        if ($item->getUri() !== $this->request->getBaseUrl() . '/' && (substr($this->request->getRequestUri(), 0, strlen($item->getUri())) === $item->getUri())) {
            // URL isn't just "/" and the first part of the URL match
            return true;
        }

        return null;
    }
}
