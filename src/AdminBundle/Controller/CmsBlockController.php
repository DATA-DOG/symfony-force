<?php namespace AdminBundle\Controller;

use AdminBundle\Form\CmsBlockType;
use AppBundle\Entity\CmsBlock;
use DataDog\PagerBundle\Pagination;
use Doctrine\ORM\QueryBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CmsBlockController
 */
class CmsBlockController extends Controller
{

    /**
     * @Route("/cms", name="admin_cms_index")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $blocks = $this->get('em')->getRepository('AppBundle:CmsBlock')->createQueryBuilder('t');

        return $this->render("AdminBundle:CmsBlock:index.html.twig", [
            'blocks' => new Pagination($blocks, $request, [
                'applyFilter' => [$this, 'cmsFilters'],
            ]),
        ]);
    }

    /**
     * Displays a form to create a new CmsBlock entity.
     *
     * @Route("/cms/new", name="admin_cms_create")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request)
    {
        $user = new CmsBlock();
        $form = $this->createForm(new CmsBlockType(), $user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('admin_cms_index');
        }

        return $this->render("AdminBundle:CmsBlock:new.html.twig", [
            'entity' => $user,
            'form' => $form->createView(),
        ]);
    }


    /**
     * Displays a form to edit an existing CmsBlock entity.
     *
     * @Route("/cms/{id}/edit", name="admin_cms_edit")
     * @param CmsBlock $user
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(CmsBlock $user, Request $request)
    {
        $form = $this->createForm(new CmsBlockType(), $user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_cms_index');
        }

        return $this->render("AdminBundle:CmsBlock:edit.html.twig", [
            'form' => $form->createView(),
            'entity' => $user,
        ]);
    }

    /**
     * Deletes a CmsBlock entity.
     *
     * @Route("/cms/{id}/delete", name="admin_cms_delete")
     * @param CmsBlock $user
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(CmsBlock $user)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute('admin_cms_index');
    }

    /**
     * Our filter handler function, which allows us to
     * modify the query builder specifically for our filter option
     * @param QueryBuilder $qb
     * @param string $key
     * @param string $val
     */
    public function cmsFilters(QueryBuilder $qb, $key, $val)
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
