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
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;

class UserController extends Controller
{
    use DoctrineController;

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

        if ($error && $error->getMessageKey() === 'Invalid credentials.') {
            $error = "Is incorrect your email or password.";
        }
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

        $same = $this->repo('AppBundle:User')->findOneBy(['email' => $user->getEmail()]);
        if (null !== $same and $same->isConfirmed()) {
            $form->get('email')->addError(new FormError("Confirmed already is the email {$user->getEmail()}."));
            return $this->renderSignUp($form);
        }

        if (null !== $same) {
            $this->get('mail')->user($user, 'activate_email', [
                'link' => $this->generateUrl('app_user_confirm', ['token' => $user->getConfirmationToken()], true),
            ]);
            $this->flash('info', "To the {$user->getEmail()} address the confirmation email was resent.");
            return $this->renderSignUp($form);
        }

        $user->regenerateConfirmationToken();
        $this->persist($user);
        $this->flush();

        $this->get('mail')->user($user, 'activate_email', [
            'link' => $this->generateUrl('app_user_confirm', ['token' => $user->getConfirmationToken()], true),
        ]);

        $this->flash('success', "The confirmation email should soon be received.");
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

        $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
        $user->setPassword($encoder->encodePassword($user->getPlainPassword(), $user->getSalt()));
        $user->confirm();
        $this->persist($user);
        $this->flush();

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);

        $this->flash('success', "The user {$user} confirmed may be.");
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
        $this->persist($em->merge($user));
        $this->flush();

        $this->flash('success', "Updated your profile may be.");
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

        $email = $form->get('email')->getData();
        $user = $this->repo('AppBundle:User')->findOneByEmail($email);

        if (!$user) {
            $form->get('email')->addError(new FormError("Not found is the user by email given."));
            return ['form' => $form->createView()];
        }

        // @TODO: expiration date may be useful
        $user->regenerateConfirmationToken();
        $this->persist($user);
        $this->flush();

        // @TODO: captcha after 3 failed attempts
        $this->get('mail')->user($user, 'activate_email', [
            'link' => $this->generateUrl('app_user_confirm', ['token' => $user->getConfirmationToken()], true),
        ]);

        $this->flash('success', "User password reset requested may be.");
        return $this->redirect($this->generateUrl('app_user_login'));
    }
}
