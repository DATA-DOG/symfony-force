<?php namespace AdminBundle\Controller;

use AppBundle\Controller\Controller;
use AppBundle\Entity\User;
use AdminBundle\Form\UserType;
use DataDog\PagerBundle\Pagination;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UserController
 */
class UserController extends Controller
{

    /**
     * @Route("/user", name="admin_user_index")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $users = $this->get('em')->getRepository('AppBundle:User')->createQueryBuilder('u');

        return $this->render("AdminBundle:User:index.html.twig", [
            'users' => new Pagination($users, $request),
        ]);
    }

    /**
     * Displays a form to create a new User entity.
     *
     * @Route("/user/new", name="admin_user_create")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(new UserType(), $user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->encodePassword($user);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('admin_user_index');
        }

        return $this->render("AdminBundle:User:new.html.twig", [
            'entity' => $user,
            'form' => $form->createView(),
        ]);
    }


    /**
     * Displays a form to edit an existing User entity.
     *
     * @Route("/user/{id}/edit", name="admin_user_edit")
     * @param User $user
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(User $user, Request $request)
    {
        $form = $this->createForm(new UserType(), $user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->encodePassword($user);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_user_index');
        }

        return $this->render("AdminBundle:User:edit.html.twig", [
            'form' => $form->createView(),
            'entity' => $user,
        ]);
    }

    /**
     * Deletes a User entity.
     *
     * @Route("/user/{id}/delete", name="admin_user_delete")
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(User $user)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();

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

}
