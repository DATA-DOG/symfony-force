<?php

namespace ApiBundle\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Translation\TranslatorInterface;
use JsonSerializable;

class ApiResponseListener
{
    private $logger;
    private $environment;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct($environment, TranslatorInterface $translator, LoggerInterface $logger = null)
    {
        $this->environment = $environment;
        $this->logger = $logger;
        $this->translator = $translator;
    }

    protected function isApiRequest(Request $request)
    {
        // @NOTE: you should identify request whether it is api or not
        // in this case it has prefix /api
        return strpos($request->getRequestUri(), '/api') === 0;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (!$this->isApiRequest($request)) {
            return;
        }

        // request language
        if ($request->headers->has('Language')) {
            $this->translator->setLocale($request->headers->get('Language'));
            $request->setLocale($request->headers->get('Language'));
        }

        // request content type
        if ($type = $request->getContentType()) {
            switch ($type) {
            case 'json':
                $request->setRequestFormat('json');
                break;
            default:
                $mime = $request->headers->get('Content-Type');
                throw new HttpException(406, "The content type: \"{$type}\" specified as mime \"{$mime}\" - is not supported.");
            }
        } else {
            // default format is JSON
            $request->setRequestFormat('json');
        }

        // request accept content type, currently only JSON
        $accepts = $request->getAcceptableContentTypes();
        $types = array_filter(array_unique(array_map([$request, 'getFormat'], $accepts)));

        if ($types && !in_array('json', $types, true)) {
            $acceptable = implode(',', $accepts);
            throw new HttpException(406, "None of acceptable content types: {$acceptable} are supported.");
        }

        // if there is a body, decode it currently as JSON only
        if ($content = $request->getContent()) {
            $data = @json_decode($content, true);

            if (null === $data) {
                // the error may be important, log it
                if (null !== $this->logger) {
                    $this->logger->error("Failed to parse json request content, err: " . json_last_error_msg());
                }

                throw new HttpException(400, "The given content is not a valid json.");
            }
            $request->request = new ParameterBag($data);
        }
    }

    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();
        if (!$this->isApiRequest($request)) {
            return;
        }
        // we now only use json, if more formats will be added, then it can check request uri or headers
        // and only presenter object allowed as controller result etc
        $data = $event->getControllerResult();

        switch (true) {
        case is_array($data):
        case $data instanceof JsonSerializable:
            $response = new JsonResponse($data);
            break;
        case is_string($data):
            $response = new Response($data);
            $response->headers->set('Content-Type', 'application/json');
            break;
        default:
            throw new \UnexpectedValueException("Response type: " . gettype($data) . " from controller was not expected.");
        }

        $response->headers->set('Language', $this->translator->getLocale());
        $event->setResponse($response);
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $request = $event->getRequest();
        if (!$this->isApiRequest($request)) {
            return;
        }
        // handle only api scope
        $error = [
            'code' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            'message' => "You've come across an application error. Our support team will be receiving this error shortly.",
        ];

        $exception = $event->getException();

        if ($exception instanceof AccessDeniedHttpException) {
            $error['code'] = $exception->getStatusCode();
            $error['message'] = "Your account does not have the required roles. To access this resource.";
        } elseif ($exception instanceof AuthenticationException) {
            $error['code'] = JsonResponse::HTTP_UNAUTHORIZED;
            $error['message'] = "Authentication is required.";
        } elseif ($exception instanceof HttpException) {
            $error['code'] = $exception->getStatusCode();
            $error['message'] = $exception->getMessage();
        } elseif (in_array($this->environment, ['dev'])) {
            $error['message'] = $exception->getMessage();
        }

        if ($error['code'] >= 500) {
            $this->logger->error($exception);
        } else {
            $this->logger->debug($exception);
        }

        $event->setResponse(new JsonResponse(compact('error'), $error['code']));
    }
}
