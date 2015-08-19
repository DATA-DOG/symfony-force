<?php

namespace ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Controller\DoctrineController;
use AppBundle\Entity\CmsBlock;
use ApiBundle\Resource\CmsBlock\ListResource;
use ApiBundle\Resource\CmsBlock\SingleResource;
use DataDog\PagerBundle\Pagination;

/**
 * @Route("/cms-blocks")
 */
class CmsBlockController extends Controller
{
    use DoctrineController;

    /**
     * @Route
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        // pagination can be handled by request query parameters: page, limit, filters and sorters
        $qb = $this->repo('AppBundle:CmsBlock')->createQueryBuilder('t');
        $paged = new Pagination($qb, $request);

        return new ListResource(iterator_to_array($paged, false));
    }

    /**
     * @Route("/{id}")
     * @Method("GET")
     */
    public function viewAction(Request $request, CmsBlock $block)
    {
        return new SingleResource($block);
    }
}
