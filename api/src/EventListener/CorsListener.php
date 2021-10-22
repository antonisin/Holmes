<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Cors inter-domains listener class.
 * This class is implemented as event listener to resolve problems with browser and inter-domains CORS rules.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.0.0
 */
class CorsListener
{
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

            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Allow-Methods', 'POST, GET, PUT, DELETE, PATCH, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-AUTH-TOKEN, tus-resumable, upload-length, upload-metadata, upload-offset, upload-concat');
            $response->headers->set('Access-Control-Max-Age', 3600);
            $response->headers->set('Access-Control-Allow-Origin', $request->headers->get('origin') ?? '*');

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
        $request = $event->getRequest();

        $response = $event->getResponse();

        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-AUTH-TOKEN, tus-resumable, upload-length, upload-metadata, upload-offset, upload-concat');
        $response->headers->set('Access-Control-Allow-Origin', $request->headers->get('origin') ?? '*');
        $response->headers->set('Access-Control-Allow-Methods', 'POST, GET, PUT, DELETE, PATCH, OPTIONS');
        $response->headers->set('Vary', 'Origin');

        $event->setResponse($response);
    }
}
