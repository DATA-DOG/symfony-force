<?php

namespace AdminBundle\Twig;

use DataDog\AuditBundle\Entity\AuditLog;

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
        ];
    }

    public function audit(\Twig_Environment $twig, AuditLog $log)
    {
        return $twig->render("AdminBundle::Audit/{$log->getAction()}.html.twig", compact('log'));
    }

    public function value(\Twig_Environment $twig, $val)
    {
        if (is_bool($val)) {
            return $val ? 'true' : 'false';
        } elseif (is_array($val)) {
            return $twig->render("AdminBundle::Audit/association.html.twig", compact('val'));
        }
        return $val;
    }

    public function getName()
    {
        return 'admin_audit_extension';
    }
}
