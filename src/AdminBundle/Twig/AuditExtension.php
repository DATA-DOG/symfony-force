<?php

namespace AdminBundle\Twig;

use DataDog\AuditBundle\Entity\AuditLog;
use DataDog\AuditBundle\Entity\Association;

class AuditExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        $defaults = [
            'is_safe' => ['html'],
            'needs_environment' => true,
        ];

        return [
            new \Twig_SimpleFunction('audit', [$this, 'audit'], $defaults),
            new \Twig_SimpleFunction('audit_value', [$this, 'value'], $defaults),
            new \Twig_SimpleFunction('audit_assoc', [$this, 'assoc'], $defaults),
            new \Twig_SimpleFunction('audit_blame', [$this, 'blame'], $defaults),
        ];
    }

    public function audit(\Twig_Environment $twig, AuditLog $log)
    {
        return $twig->render("AdminBundle:Audit:{$log->getAction()}.html.twig", compact('log'));
    }

    public function assoc(\Twig_Environment $twig, $assoc)
    {
        return $twig->render("AdminBundle:Audit:assoc.html.twig", compact('assoc'));
    }

    public function blame(\Twig_Environment $twig, $blame)
    {
        return $twig->render("AdminBundle:Audit:blame.html.twig", compact('blame'));
    }

    public function value(\Twig_Environment $twig, $val)
    {
        switch (true) {
        case is_bool($val):
            return $val ? 'true' : 'false';
        case is_array($val) && isset($val['fk']):
            return $this->assoc($twig, $val);
        case is_array($val):
            return json_encode($val);
        case is_string($val):
            return strlen($val) > 60 ? substr($val, 0, 60) . '...' : $val;
        case is_null($val):
            return 'NULL';
        default:
            return $val;
        }
    }

    public function getName()
    {
        return 'admin_audit_extension';
    }
}
