<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Gedmo\Translatable\TranslatableListener;

class DoctrineExtensionsListener
{
    private $translatable;

    public function __construct(TranslatableListener $translatable)
    {
        $this->translatable = $translatable;
    }

    public function onLateKernelRequest(GetResponseEvent $event)
    {
        $this->translatable->setTranslatableLocale($event->getRequest()->getLocale());
    }
}
