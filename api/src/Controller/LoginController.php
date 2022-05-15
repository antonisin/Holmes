<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Login controller class.
 * This class is implemented as symfony controller to rule login process, render login form and process social network
 * authentication. Need to keep in mind that real authentication is managed by service Authentication class.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.0.0
 */
class LoginController extends AbstractController
{
    /**
     * Describe OAuth rights to access on authentication request.
     */
    private const OAUTH_RIGHT = [
        'facebook'      => ['public_profile', 'email'],
        'google'        => ['profile', 'email'],
        'vkontakte'     => ['email', 'status'],
        'odnoklassniki' => ['VALUABLE_ACCESS'],
    ];


    /**
     * Login page.
     * This method is used render login page form.
     *
     * @return Response
     */
    #[
        Route("/login/", name: 'login'),
        Route("/login", name: 'login_1')
    ]
    public function indexAction(): Response
    {
        return $this->render('login.html.twig');
    }

    /**
     * Logout path.
     * This method do not any actions and used just to define path of logout process.
     * @return void
     */
    #[Route("/logout", name: 'logout')]
    public function logoutAction()
    {
    }

    /**
     * Social authentication connect method.
     * This method is used for authentication via social network, to get right client and return redirect to social
     * network second part authentication page.
     *
     * @param string         $_route         - Current route name. Needed to extract social network name/slug/key.
     * @param ClientRegistry $clientRegistry - Client registry service instance to get social network client instance.
     *
     * @return RedirectResponse
     */
    #[
        Route("/connect/google", name: "connect_google"),
        Route("/connect/facebook", name: "connect_facebook"),
        Route("/connect/vk", name: "connect_vkontakte"),
        Route("/connect/ok", name: "connect_odnoklassniki")
    ]
    public function connectAction(string $_route, ClientRegistry $clientRegistry): RedirectResponse
    {
        $clientType = str_replace('connect_', '', $_route);

        return $clientRegistry->getClient($clientType)->redirect(self::OAUTH_RIGHT[$clientType]);
    }

    /**
     * Google check callback authentication endpoint.
     * This path/method is used just to describe routes and define authentication(s). The true authentication is
     * processing in security Authenticator service/class.
     *
     * @return void
     */
    #[
        Route("/connect/check/google", name: "connect_check_google"),
        Route("/connect/check/facebook", name: "connect_check_facebook"),
        Route("/connect/check/vk", name: "connect_check_vkontakte"),
        Route("/connect/check/ok", name: "connect_check_odnoklassniki"),
    ]
    public function connectCheckAction()
    {
    }
}
