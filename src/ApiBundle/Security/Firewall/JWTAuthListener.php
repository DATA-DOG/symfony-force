<?php

namespace ApiBundle\Security\Firewall;

use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use ApiBundle\Security\Authentication\Token\JWTUserToken;

class JWTAuthListener implements ListenerInterface
{
    private $providerKey;

    private $privkey;

    private $passphrase;

    /**
     * @var AuthenticationManagerInterface
     */
    private $authenticationManager;

    /**
     * @param AuthenticationManagerInterface $authenticationManager
     * @param $providerKey
     */
    public function __construct(AuthenticationManagerInterface $authenticationManager, $providerKey, array $config)
    {
        $this->authenticationManager = $authenticationManager;
        $this->providerKey = $providerKey;
        $this->privkey = 'file://' . realpath($config['priv_key']);
        $this->passphrase = $config['passphrase'];
    }

    /**
     * {@inheritdoc}
     */
    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->isMethod('POST')) {
            throw new HttpException(405, "Only POST method is allowed for JWT authentication");
        }

        $username = $request->request->get('username', null);
        $password = $request->request->get('password', null);

        try {
            $token = $this->authenticationManager->authenticate(new UsernamePasswordToken($username, $password, $this->providerKey));
        } catch (\InvalidArgumentException $e) {
            // most probably failed to find user by these credentials
            // let other unexpected exceptions pass through
            throw new HttpException(JsonResponse::HTTP_UNAUTHORIZED, "Username or password is not valid.", $e);
        }

        $user = $token->getUser();

        $header = [];

        // jwt token data
        $payload = [
            'username' => $user->getUsername(),
            'exp' => (new \DateTime('+1 day'))->format('U'),
            'iat' => (new \DateTime('now'))->format('U'),
        ];

        // build jwt data to sign
        $toSign = implode('.', array_map('base64_encode', array_map('json_encode', [$header, $payload])));

        // init openssl private key resource
        $key = openssl_pkey_get_private($this->privkey, $this->passphrase);
        if (!is_resource($key)) {
            throw new HttpException(500, "not valid private key, {$this->privkey}");
        }

        // ensure key is valid RSA private key
        if (openssl_pkey_get_details($key)['type'] !== JWTUserToken::KEY_TYPE) {
            throw new HttpException(500, "Only RSA keys are supported.");
        }

        // create signature
        $signature = null;
        if (!openssl_sign($toSign, $signature, $key, JWTUserToken::ALGO)) {
            throw new HttpException(500, "could not sign JWT.");
        }

        // create jwt token
        $jwt = implode('.', [$toSign, base64_encode($signature)]);

        // finally create response
        $event->setResponse(new JsonResponse([
            'token' => $jwt,
            'id' => $user->getId(),
            'roles' => $user->getRoles(),
        ]));
    }
}
