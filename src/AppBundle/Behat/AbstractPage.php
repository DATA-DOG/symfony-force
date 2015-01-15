<?php

namespace AppBundle\Behat;

use Behat\Mink\Session;

abstract class AbstractPage
{
    protected $session;
    protected $path;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function open($path)
    {
        $this->path = $path;
        $this->session->visit($path);
        return $this;
    }

    public function isOpen()
    {
        return strlen($this->path) !== 0 and preg_match("@{$this->path}$@", $this->session->getCurrentUrl());
    }

    abstract public function route();
}
