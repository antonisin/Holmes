<?php
namespace App\EventListener;

use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Request listener class.
 * This class is implemented as symfony listener on requests. For base porpoise, listener was created to redirect users
 * on auth fail, un-authorized user or page not found.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.0.0
 */
class RequestListener implements EventSubscriberInterface
{
    /**
     * Router generator service.
     * This property contains router generator service used to generate urls and paths.
     *
     * @var UrlGeneratorInterface
     */
    private UrlGeneratorInterface $router;

    /**
     * Session service.
     * This property contain session service used to work with user session and flash messages.
     *
     * @var SessionInterface
     */
    private SessionInterface $session;


    /**
     * Default method constructor.
     * This method is used to initialize class, extract all main services and define all needed properties.
     *
     * @param UrlGeneratorInterface $router       - Router generator used to generate urls and paths.
     * @param RequestStack          $requestStack - Request stack service used to work with requests and session.
     */
    public function __construct(UrlGeneratorInterface $router, RequestStack $requestStack)
    {
        $this->router  = $router;
        $this->session = $requestStack->getSession();
    }

    /**
     * {@inheritDoc}
     */
    #[ArrayShape([KernelEvents::EXCEPTION => 'array'])]
    public static function getSubscribedEvents(): array
    {
        return [ KernelEvents::EXCEPTION => ['onKernelException', 2] ];
    }

    /**
     * Method called on exception.
     * This method is called on application or system exception based on page or user's state.
     *
     * @param ExceptionEvent $event - Exception or application event.
     *
     * @return void
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if ($exception instanceof AccessDeniedException) {
            $this->session->getFlashBag()->add('error', 'Unauthorized User. Please login again');
            $event->setResponse(new RedirectResponse($this->router->generate('login')));
        } elseif ($exception instanceof NotFoundHttpException) {
            $this->session->getFlashBag()->add('error', 'Page Not Found');
            $event->setResponse(new RedirectResponse($this->router->generate('login')));
        }
    }
}