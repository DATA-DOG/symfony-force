<?php

namespace AppBundle\Behat;

use Behat\Mink\Session;
use Symfony\Component\Routing\RouterInterface;

abstract class AbstractPage
{
    protected $session;
    protected $router;

    public function __construct(Session $session, RouterInterface $router)
    {
        $this->session = $session;
        $this->router = $router;
    }

    public function __call($method, array $args = [])
    {
        return call_user_func_array([$this->session->getPage(), $method], $args);
    }

    public function open(array $params = [])
    {
        $this->session->visit($this->router->generate($this->route(), $params));
        return $this;
    }

    public function isOpen(array $params = [])
    {
        $path = $this->router->generate($this->route(), $params);
        return preg_match("@{$path}$@", $this->session->getCurrentUrl());
    }

    public function mustBeOpen(array $params = [])
    {
        $path = $this->router->generate($this->route(), $params);
        if (!preg_match("@{$path}$@", $this->session->getCurrentUrl())) {
            throw new \RuntimeException("The page is not open, actual path: {$this->session->getCurrentUrl()} does not match with: {$path}.");
        }
        return $this;
    }

    protected function xpath($xpath)
    {
        return $this->session->getPage()->find('xpath', $xpath);
    }

    protected function css($css)
    {
        return $this->session->getPage()->find('css', $css);
    }

    abstract protected function route();
}
