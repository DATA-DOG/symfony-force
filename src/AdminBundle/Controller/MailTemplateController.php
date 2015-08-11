<?php namespace AdminBundle\Controller;

use AdminBundle\Form\MailTemplateType;
use AppBundle\Entity\MailTemplate;
use DataDog\PagerBundle\Pagination;
use Doctrine\ORM\QueryBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class MailTemplateController
 */
class MailTemplateController extends Controller
{
    /**
     * @Route("/template", name="admin_template_index")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $users = $this->get('em')->getRepository('AppBundle:MailTemplate')->createQueryBuilder('t');

        return $this->render("AdminBundle:MailTemplate:index.html.twig", [
            'templates' => new Pagination($users, $request, [
                'applyFilter' => [$this, 'templateFilters'],
            ]),
        ]);
    }

    /**
     * Displays a form to create a new MailTemplate entity.
     *
     * @Route("/template/new", name="admin_template_create")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request)
    {
        $user = new MailTemplate();
        $form = $this->createForm(new MailTemplateType(), $user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('admin_template_index');
        }

        return $this->render("AdminBundle:MailTemplate:new.html.twig", [
            'entity' => $user,
            'form' => $form->createView(),
        ]);
    }


    /**
     * Displays a form to edit an existing MailTemplate entity.
     *
     * @Route("/template/{id}/edit", name="admin_template_edit")
     * @param MailTemplate $user
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(MailTemplate $user, Request $request)
    {
        $form = $this->createForm(new MailTemplateType(), $user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_template_index');
        }

        return $this->render("AdminBundle:MailTemplate:edit.html.twig", [
            'form' => $form->createView(),
            'entity' => $user,
        ]);
    }

    /**
     * Deletes a MailTemplate entity.
     *
     * @Route("/template/{id}/delete", name="admin_template_delete")
     * @param MailTemplate $user
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(MailTemplate $user)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute('admin_template_index');
    }

    /**
     * @param QueryBuilder $qb
     * @param $key
     * @param $val
     */
    public function templateFilters(QueryBuilder $qb, $key, $val)
    {
        if (!$val) {
            return;
        }

        switch ($key) {
            case 't.alias':
                $qb->andWhere($qb->expr()->like('t.alias', ':alias'))->setParameter('alias', "%$val%");
                break;
            case 't.subject':
                $qb->andWhere($qb->expr()->like('t.subject', ':subject'))->setParameter('subject', "%$val%");
                break;
            case 't.updatedAt':
                $date = date("Y-m-d", strtotime($val));
                $qb->andWhere($qb->expr()->gt('t.updatedAt', "'$date 00:00:00'"));
                $qb->andWhere($qb->expr()->lt('t.updatedAt', "'$date 23:59:59'"));
                break;
        }
    }
}
