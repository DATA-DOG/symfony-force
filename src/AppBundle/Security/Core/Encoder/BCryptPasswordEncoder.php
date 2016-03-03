<?php

namespace AppBundle\Security\Core\Encoder;

use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder as BaseBCryptPasswordEncoder;

class BCryptPasswordEncoder extends BaseBCryptPasswordEncoder
{
    public function encodePassword($raw, $salt)
    {
        // Ignore $salt, the auto-generated one is always the best
        return parent::encodePassword($raw, "");
    }
}
