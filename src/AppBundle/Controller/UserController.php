<?php

namespace AppBundle\Controller;

use AppBundle\Form\Type\User\RegisterType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;

class UserController extends Controller
{
    /**
     * @Route("/login")
     * @Method("GET")
     * @Template
     */
    public function loginAction(Request $request)
    {
        $session = $request->getSession();
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } elseif (null !== $session && $session->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = '';
        }

        // show messages for standard security exceptions
        if ($error instanceof BadCredentialsException or $error instanceof InvalidCsrfTokenException) {
            $error = $error->getMessage();
        }

        // if there was an unexpected exception, log it, and do not show it's message to user
        if ($error instanceof \Exception) {
            $this->get('logger')->error($error->getMessage());
            $error = 'Unexpected error occured while trying to login..';
        }

        // last username entered by the user
        $username = (null === $session) ? '' : $session->get(SecurityContext::LAST_USERNAME);
        $csrf_token = $this->container->get('form.csrf_provider')->generateCsrfToken('authenticate');

        return compact('username', 'error', 'csrf_token');
    }

    /**
     * @Route("/register")
     * @Method({"GET", "POST"})
     * @Template
     */
    public function registerAction(Request $request)
    {
        $form = $this->createForm(new RegisterType(), $user = new User());
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->flash('success', "User was successfully created, you will receive email soon.");
            return $this->redirect($this->generateUrl('app_user_login'));
        }

        return ['form' => $form->createView()];
    }
}
