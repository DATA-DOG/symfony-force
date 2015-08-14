<?php

namespace AdminBundle\Controller;

use AdminBundle\Form\CmsBlockType;
use AppBundle\Entity\CmsBlock;
use AppBundle\Controller\DoctrineController;
use DataDog\PagerBundle\Pagination;
use Doctrine\ORM\QueryBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/cms")
 */
class CmsBlockController extends Controller
{
    use DoctrineController;

    /**
     * @Route("/")
     * @Method("GET")
     * @Template
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    function indexAction(Request $request)
    {
        $blocks = $this->get('em')->getRepository('AppBundle:CmsBlock')->createQueryBuilder('t');

        return [
            'blocks' => new Pagination($blocks, $request, [
                'applyFilter' => [$this, 'cmsFilters'],
            ]),
        ];
    }

    /**
     * Displays a form to create a new CmsBlock entity.
     *
     * @Route("/new")
     * @Method({"GET", "POST"})
     * @Template
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    function newAction(Request $request)
    {
        $block = new CmsBlock();
        $form = $this->createForm(new CmsBlockType(), $block);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            return [
                'entity' => $block,
                'form' => $form->createView(),
            ];
        }

        $this->persist($block);
        $this->flush();
        $this->addFlash("success", "Created was the cms block: {$block->getName()}");

        return $this->redirectToRoute('admin_cmsblock_index');
    }


    /**
     * Displays a form to edit an existing CmsBlock entity.
     *
     * @Route("/{id}/edit")
     * @Method({"GET", "POST"})
     * @Template
     *
     * @param CmsBlock $block
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    function editAction(CmsBlock $block, Request $request)
    {
        $form = $this->createForm(new CmsBlockType(), $block);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            return [
                'form' => $form->createView(),
                'entity' => $block,
            ];
        }
        $this->persist($block);
        $this->flush();
        // refresh cache
        $this->get('cache.default')->delete('cms_block.'.$block->getAlias());
        $this->addFlash("success", "Updated was the cms block: {$block->getName()}");

        return $this->redirectToRoute('admin_cmsblock_index');
    }

    /**
     * Deletes a CmsBlock entity.
     *
     * @Route("/{id}/delete")
     * @Method("GET")
     *
     * @param CmsBlock $block
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    function deleteAction(CmsBlock $block)
    {
        $this->remove($block);
        $this->flush();
        $this->addFlash("danger", "Removed was the cms block: {$block->getName()}");

        return $this->redirectToRoute('admin_cmsblock_index');
    }

    /**
     * Our filter handler function, which allows us to
     * modify the query builder specifically for our filter option
     * @param QueryBuilder $qb
     * @param string $key
     * @param string $val
     */
    function cmsFilters(QueryBuilder $qb, $key, $val)
    {
        if (empty($val)) {
            return;
        }

        switch ($key) {
            case 't.alias':
                $qb->andWhere($qb->expr()->like('t.alias', ':alias'));
                $qb->setParameter('alias', "%$val%");
                break;
            case 't.name':
                $qb->andWhere($qb->expr()->like('t.name', ':name'));
                $qb->setParameter('name', "%$val%");
                break;
            case 't.updatedAt':
                $date = date("Y-m-d", strtotime($val));
                $qb->andWhere($qb->expr()->gt('t.updatedAt', "'$date 00:00:00'"));
                $qb->andWhere($qb->expr()->lt('t.updatedAt', "'$date 23:59:59'"));
                break;
        }
    }
}
