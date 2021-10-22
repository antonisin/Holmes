<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\LogoutException;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Security\Http\HttpUtils;

/**
 */
class LogoutListener extends \Symfony\Component\Security\Http\Firewall\LogoutListener
{
    protected $tokenStorage;
    protected $eventDispatcher;

    public function __construct(TokenStorageInterface $tokenStorage, HttpUtils $httpUtils, $eventDispatcher, array $options = [], CsrfTokenManagerInterface $csrfTokenManager = null)
    {
        parent::__construct($tokenStorage, $httpUtils, $eventDispatcher, $options, $csrfTokenManager);
        $this->tokenStorage = $tokenStorage;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Performs the logout if requested.
     *
     * If a CsrfTokenManagerInterface instance is available, it will be used to
     * validate the request.
     *
     * @throws LogoutException   if the CSRF token is invalid
     * @throws \RuntimeException if the LogoutSuccessHandlerInterface instance does not return a response
     */
    public function authenticate(RequestEvent $event)
    {
        $request = $event->getRequest();

        if ($this->tokenStorage) {
            $logoutEvent = new LogoutEvent($request, $this->tokenStorage->getToken());
            $this->eventDispatcher->dispatch($logoutEvent);
            $this->tokenStorage->setToken(null);
        }

        $event->setResponse(new Response());
    }

}