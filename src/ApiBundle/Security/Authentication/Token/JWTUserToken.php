<?php

namespace ApiBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class JWTUserToken extends AbstractToken
{
    const KEY_TYPE = OPENSSL_KEYTYPE_RSA;
    const ALGO = OPENSSL_ALGO_SHA256;

    /**
     * @var string
     */
    protected $rawToken;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $roles = [])
    {
        parent::__construct($roles);
        $this->setAuthenticated(count($roles) > 0);
    }

    /**
     * @param string $rawToken
     */
    public function setRawToken($rawToken)
    {
        $this->rawToken = $rawToken;
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials()
    {
        return $this->rawToken;
    }
}
