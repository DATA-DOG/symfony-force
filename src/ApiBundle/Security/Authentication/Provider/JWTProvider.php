<?php

namespace ApiBundle\Security\Authentication\Provider;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use ApiBundle\Security\Authentication\Token\JWTUserToken;

class JWTProvider implements AuthenticationProviderInterface
{
    /**
     * @var UserProviderInterface
     */
    protected $userProvider;

    protected $pubkey;

    /**
     * @param UserProviderInterface $userProvider
     */
    public function __construct(UserProviderInterface $userProvider, array $config)
    {
        $this->userProvider = $userProvider;
        $this->pubkey = 'file://' . realpath($config['pub_key']);
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token)
    {
        $parts = explode('.', $token->getCredentials());
        if (count($parts) != 3) {
            throw new AuthenticationException("Incorrect JWT token, does not match required number of parts");
        }

        // base64 decode jwt token parts
        list($header, $payload, $signature) = array_map('base64_decode', $parts);

        // signable parts
        $signup = implode('.', [$parts[0], $parts[1]]);

        // init openssl public key resource
        $key = $key = openssl_pkey_get_public($this->pubkey);
        if (!is_resource($key)) {
            throw new AuthenticationException("Not valid pub key: {$this->pubkey}");
        }

        // ensure key is valid RSA public key
        if (openssl_pkey_get_details($key)['type'] !== JWTUserToken::KEY_TYPE) {
            throw new AuthenticationException("Only RSA keys are supported.");
        }

        // verify signature
        if (!openssl_verify($signup, $signature, $key, JWTUserToken::ALGO)) {
            throw new AuthenticationException("Could not verify signature.");
        }

        // check expiration
        if (isset($payload['exp']) && is_numeric($payload['exp'])) {
            if ((new \DateTime('now'))->format('U') < $payload['exp']) {
                throw new AuthenticationException("Token has expired");
            }
        }

        // decode payload and header json
        list($payload, $header) = array_map(function($json) {
            return json_decode($json, true);
        }, [$payload, $header]);

        // validate header if necessary
        // ...

        // load user
        if (!$user = $this->userProvider->loadUserByUsername($payload['username'])) {
            throw new AuthenticationException("user does not exist");
        }

        $authToken = new JWTUserToken($user->getRoles());
        $authToken->setUser($user);
        $authToken->setRawToken($token->getCredentials());
        return $authToken;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof JWTUserToken;
    }
}
