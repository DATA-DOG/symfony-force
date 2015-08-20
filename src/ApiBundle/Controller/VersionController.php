<?php

namespace ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use ApiBundle\Resource\VersionResource;

/**
 * @Route("/version")
 */
class VersionController extends Controller
{
    /**
     * @Route
     * @Method("GET")
     */
    public function indexAction()
    {
        $pkg = realpath($this->getParameter('kernel.root_dir') . '/../package.json');
        if (!$pkg) {
            throw new $this->createNotFoundException("package.json was not found in project root.");
        }

        return new VersionResource(json_decode(file_get_contents($pkg), true));
    }
}
