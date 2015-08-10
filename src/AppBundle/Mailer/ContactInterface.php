<?php

namespace AppBundle\Mailer;

interface ContactInterface
{
    /**
     * @return string
     */
    public function getEmail();

    /**
     * @return string
     */
    public function getFullName();
}
