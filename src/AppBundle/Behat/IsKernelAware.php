<?php

namespace AppBundle\Behat;

trait IsKernelAware
{
    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * {@inheritdoc}
     */
    public function setKernel(\Symfony\Component\HttpKernel\KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Returns Container instance.
     *
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->kernel->getContainer();
    }

    /**
     * Get service by id.
     *
     * @param string $id
     * @return object
     */
    protected function get($id)
    {
        return $this->getContainer()->get($id);
    }

    protected function getParameter($name)
    {
        return $this->getContainer()->getParameter($name);
    }

    protected function mustGetUser()
    {
        if (!$token = $this->get('security.context')->getToken()) {
            throw new \RuntimeException("There is no user logged in at the moment. Security context is empty.");
        }
        return $token->getUser();
    }
}
