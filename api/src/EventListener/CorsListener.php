<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Cors inter-domains listener class.
 * This class is implemented as event listener to resolve problems with browser and inter-domains CORS rules.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.1.0
 */
class CorsListener
{
    const ALLOWED_HEADERS = [
        'Origin',
        'Content-Type',
        'Accept',
        'Authorization',
        'X-AUTH-TOKEN',
        'upload-length',
        'upload-metadata',
        'upload-offset',
        'upload-concat',
    ];


    /**
     * Validate and resolve CORS on request received.
     *
     * @param $event - Incoming request.
     */
    public function onKernelRequest($event): void
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();
        if ('OPTIONS' === $request->getMethod()) {
            $response = new Response();
            $this->updateResponse($request, $response);
            $event->setResponse($response);
        }
    }

    /**
     * Validate and resolve CORS on response sending.
     *
     * @param $event - Event on response sending.
     */
    public function onKernelResponse($event): void
    {
        $response = $event->getResponse();
        $this->updateResponse($event->getRequest(), $response);


        $event->setResponse($response);
    }

    /**
     * Update response for cors reasons.
     *
     * @param Request|mixed  $request  - Client request instance.
     * @param Response|mixed $response - Response instance.
     * @return void
     */
    private function updateResponse(mixed $request, mixed $response)
    {
        $response->headers->set('Vary', 'Origin');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Allow-Methods', 'POST, GET, PUT, DELETE, PATCH, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', implode(',', self:: ALLOWED_HEADERS));
        $response->headers->set('Access-Control-Max-Age', 3600);
        $response->headers->set('Access-Control-Allow-Origin', $request->headers->get('origin') ?? '*');
    }
}
