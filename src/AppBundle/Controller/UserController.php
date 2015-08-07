<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\Type\User\SignupType;
use AppBundle\Form\Type\User\ConfirmType;
use AppBundle\Form\Type\User\ProfileType;
use AppBundle\Form\Type\User\ResetType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
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
    public function loginAction()
    {
        $authenticationUtils = $this->get('security.authentication_utils');
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('AppBundle:User:login.html.twig', [
            'lastUsername' => $lastUsername,
            'error' => $error
        ]);
    }

    /**
     * @Route("/signup")
     * @Method({"GET", "POST"})
     */
    public function signupAction(Request $request)
    {
        $form = $this->createForm(new SignupType(), $user = new User());

        $form->handleRequest($request);
        if (!$form->isValid()) {
            return $this->renderSignUp($form);
        }

        $em = $this->getDoctrine()->getManager();
        $same = $em->getRepository('AppBundle:User')->findOneBy(['email'=>$user->getEmail()]);
        if (null !== $same and $same->isConfirmed()) {
            $msg = $this->get('translator')->trans('form.signup.already_confirmed', ['%email%' => $user->getEmail()]);
            $form->get('email')->addError(new FormError($msg));
            return $this->renderSignUp($form);
        }

        if (null !== $same) {
            // @TODO: resend confirmation email
            $this->flash('info', "flashes.info.user_confirmation_resent", ['%email%' => $user->getEmail()]);
            return $this->renderSignUp($form);
        }

        $user->regenerateConfirmationToken();
        $em->persist($user);
        $em->flush();

        // @TODO: send an email message with confirmation uri
        $this->flash('success', "flashes.success.user_signup");
        return $this->redirect($this->generateUrl('app_user_login'));
    }

    private function renderSignUp(FormInterface $form)
    {
        return $this->render('AppBundle:User:signup.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/confirm/{token}")
     * @Method({"GET", "POST"})
     * @ParamConverter("user", class="AppBundle:User", options={"mapping": {"token": "confirmationToken"}})
     * @Template
     */
    public function confirmAction(Request $request, User $user)
    {
        $form = $this->createForm(new ConfirmType(), $user);
        $form->handleRequest($request);
        if (!$form->isValid()) {
            return ['form' => $form->createView(), 'token' => $user->getConfirmationToken()];
        }

        $em = $this->getDoctrine()->getManager();
        $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
        $user->setPassword($encoder->encodePassword($user->getPlainPassword(), $user->getSalt()));
        $user->confirm();
        $em->persist($user);
        $em->flush();

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);

        $this->flash('success', "flashes.success.user_confirmed", ['%name%' => $user]);
        return $this->redirect($this->generateUrl('app_user_profile'));
    }

    /**
     * @Route("/profile")
     * @Method({"GET", "POST"})
     * @Template
     */
    public function profileAction(Request $request)
    {
        $user = clone $this->getUser(); // prevent user change in session
        $form = $this->createForm(new ProfileType(), $user);
        $form->handleRequest($request);
        if (!$form->isValid()) {
            return ['form' => $form->createView()];
        }

        $em = $this->getDoctrine()->getManager();
        if ($user->getPlainPassword()) {
            $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
            $user->setPassword($encoder->encodePassword($user->getPlainPassword(), $user->getSalt()));
        }
        $em->persist($em->merge($user));
        $em->flush();

        $this->flash('success', "flashes.success.user_profile_updated");
        return $this->redirect($this->generateUrl('app_user_profile'));
    }

    /**
     * @Route("/reset")
     * @Method({"GET", "POST"})
     * @Template
     */
    public function resetAction(Request $request)
    {
        $form = $this->createForm(new ResetType());
        $form->handleRequest($request);
        if (!$form->isValid()) {
            return ['form' => $form->createView()];
        }

        $em = $this->getDoctrine()->getManager();
        $email = $form->get('email')->getData();
        $user = $em->getRepository('AppBundle:User')->findOneByEmail($email);

        if (!$user) {
            $form->get('email')->addError(new FormError($this->get('translator')->trans('form.reset.not_found')));
            return ['form' => $form->createView()];
        }

        // @TODO: expiration date may be useful
        $user->regenerateConfirmationToken();
        $em->persist($user);
        $em->flush();

        // @TODO: captcha after 3 failed attempts

        // @TODO: send email
        $this->flash('success', "flashes.success.user_reset_requested", ['%email%' => $email]);
        return $this->redirect($this->generateUrl('app_user_login'));
    }
}
