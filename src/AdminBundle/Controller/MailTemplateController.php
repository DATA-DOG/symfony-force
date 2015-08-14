<?php

namespace AdminBundle\Controller;

use AppBundle\Controller\DoctrineController;
use AdminBundle\Form\MailTemplateType;
use AppBundle\Entity\MailTemplate;
use DataDog\PagerBundle\Pagination;
use Doctrine\ORM\QueryBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/mail-template")
 */
class MailTemplateController extends Controller
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
    public function indexAction(Request $request)
    {
        $qb = $this->repo('AppBundle:MailTemplate')->createQueryBuilder('t');

        return [
            'templates' => new Pagination($qb, $request, [
                'applyFilter' => [$this, 'templateFilters'],
            ]),
        ];
    }

    /**
     * Displays a form to create a new MailTemplate entity.
     *
     * @Route("/new")
     * @Method({"GET", "POST"})
     * @Template
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request)
    {
        $template = new MailTemplate();
        $form = $this->createForm(new MailTemplateType(), $template);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            return [
                'entity' => $template,
                'form' => $form->createView(),
            ];
        }

        $this->persist($template);
        $this->flush();
        $this->addFlash("success", "Created was the email template: {$template->getSubject()}");

        return $this->redirectToRoute('admin_mailtemplate_index');
    }


    /**
     * Displays a form to edit an existing MailTemplate entity.
     *
     * @Route("/{id}/edit")
     * @Method({"GET", "POST"})
     * @Template
     *
     * @param MailTemplate $template
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(MailTemplate $template, Request $request)
    {
        $form = $this->createForm(new MailTemplateType(), $template);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            return [
                'form' => $form->createView(),
                'entity' => $template,
            ];
        }

        $this->persist($template);
        $this->flush();
        $this->addFlash("success", "Updated was the email template: {$template->getSubject()}");

        return $this->redirectToRoute('admin_mailtemplate_index');
    }

    /**
     * Deletes a MailTemplate entity.
     *
     * @Route("/{id}/delete")
     * @Method("GET")
     *
     * @param MailTemplate $template
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(MailTemplate $template)
    {
        $this->remove($template);
        $this->flush();
        $this->addFlash("danger", "Removed was the email template: {$template->getSubject()}");

        return $this->redirectToRoute('admin_mailtemplate_index');
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
