<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/two-factor")
 */
class TwoFactorController extends Controller
{
    use DoctrineController;

    /**
     * POST is handled by firewall
     *
     * @Route("/authenticate", name="app_twofactor_authenticate")
     * @Method({"GET", "POST"})
     * @Template
     */
    public function authenticateAction()
    {
        $user = $this->getUser();
        $redirectTo = $this->generateUrl('homepage');
        if ($request->getSession()->has('_security.totp.target_path')) {
            $redirectTo = $request->getSession()->get('_security.totp.target_path');
        }
        return compact('redirectTo');
    }
}
