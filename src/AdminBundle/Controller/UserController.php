<?php

namespace AdminBundle\Controller;

use AppBundle\Controller\DoctrineController;
use AppBundle\Entity\User;
use AdminBundle\Form\UserType;
use DataDog\PagerBundle\Pagination;
use Doctrine\ORM\QueryBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/user")
 */
class UserController extends Controller
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
        $users = $this->repo('AppBundle:User')->createQueryBuilder('u');

        return [
            'users' => new Pagination($users, $request, [
                'applyFilter' => [$this, 'userFilters'],
            ]),
        ];
    }

    /**
     * Displays a form to create a new User entity.
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
        $user = new User();
        $form = $this->createForm(new UserType(), $user);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            return [
                'entity' => $user,
                'form' => $form->createView(),
            ];
        }

        $this->encodePassword($user);

        $this->persist($user);
        $this->flush();
        $this->addFlash("success", "Created was the user: {$user}");

        return $this->redirectToRoute('admin_user_index');
    }


    /**
     * Displays a form to edit an existing User entity.
     *
     * @Route("/{id}/edit")
     * @Method({"GET", "POST"})
     * @Template
     *
     * @param User $user
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    function editAction(User $user, Request $request)
    {
        $form = $this->createForm(new UserType(), $user);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            return [
                'form' => $form->createView(),
                'entity' => $user,
            ];
        }

        $this->encodePassword($user);
        $this->persist($user);
        $this->flush();
        $this->addFlash("success", "Updated was the user: {$user}");

        return $this->redirectToRoute('admin_user_index');
    }

    /**
     * Deletes a User entity.
     *
     * @Route("/{id}/delete")
     * @Method("GET")
     *
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    function deleteAction(User $user)
    {
        $this->remove($user);
        $this->flush();
        $this->addFlash("danger", "Removed was the user: {$user}");

        return $this->redirectToRoute('admin_user_index');
    }

    /**
     * @param User $user
     */
    private function encodePassword(User $user)
    {
        $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
        if (!empty($user->getPlainPassword())) {
            $user->setPassword($encoder->encodePassword($user->getPlainPassword(), $user->getSalt()));
        }
    }

    /**
     * Our filter handler function, which allows us to
     * modify the query builder specifically for our filter option
     * @param QueryBuilder $qb
     * @param string $key
     * @param string $val
     */
    function userFilters(QueryBuilder $qb, $key, $val)
    {
        if (empty($val)) {
            return;
        }

        switch ($key) {
            case 'u.email':
                $qb->andWhere($qb->expr()->like('u.email', ':email'));
                $qb->setParameter('email', "%$val%");
                break;
            case 'u.createdAt':
                $date = date("Y-m-d", strtotime($val));
                $qb->andWhere($qb->expr()->gt('u.createdAt', "'$date 00:00:00'"));
                $qb->andWhere($qb->expr()->lt('u.createdAt', "'$date 23:59:59'"));
                break;
            case 'u.firstname':
                $qb->setParameter('name', "%$val%");
                $qb->andWhere($qb->expr()->orX(
                    $qb->expr()->like('u.firstname', ":name"),
                    $qb->expr()->like('u.lastname', ":name")
                ));
                break;
        }
    }
}
