<?php

namespace ApiBundle\Security\Firewall;

use ApiBundle\Security\Authentication\Token\JWTUserToken;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

class JWTListener implements ListenerInterface
{
    const HEADER_PREFIX = 'Bearer';

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var AuthenticationManagerInterface
     */
    protected $authenticationManager;

    /**
     * @param SecurityContextInterface|TokenStorageInterface $tokenStorage
     * @param AuthenticationManagerInterface                 $authenticationManager
     */
    public function __construct(TokenStorageInterface $tokenStorage, AuthenticationManagerInterface $authenticationManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        // note, if you want to allow token from query parameters or cookie, act accordingly
        if (!$request->headers->has('Authorization')) {
            throw new AuthenticationException("Authorization header is missing");
        }

        // extract parts from authorization header: prefix - jwt
        $parts = explode(' ', $request->headers->get('Authorization'));
        if (count($parts) !== 2) {
            throw new AuthenticationException("Authorization header is not valid");
        }

        // match authorization header prefix
        list($prefix, $jwt) = $parts;
        if (self::HEADER_PREFIX !== $prefix) {
            throw new AuthenticationException("Authorization header prefix is not valid");
        }

        $token = new JWTUserToken();
        $token->setRawToken($jwt);

        $authToken = $this->authenticationManager->authenticate($token);
        $this->tokenStorage->setToken($authToken);
    }
}
