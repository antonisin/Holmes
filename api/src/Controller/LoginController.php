<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.0.0
 */
class LoginController extends AbstractController
{
    #[Route("/login")]
    public function indexAction(): Response
    {
        return $this->render('login.html.twig');
    }

    #[Route("/connect/google", name: "connect_google")]
    public function connectGoogleAction(ClientRegistry $clientRegistry)
    {
        return $clientRegistry->getClient('google')->redirect([
            'email', 'profile'
        ]);
    }

    #[Route("/connect/google/check", name: "connect_google_check")]
    public function connectGoogleCheckAction(Request $request, ClientRegistry $clientRegistry)
    {
    // ** if you want to *authenticate* the user, then
        // leave this method blank and create a Guard authenticator
        // (read below)

        /** @var \KnpU\OAuth2ClientBundle\Client\Provider\GoogleClient $client */
        $client = $clientRegistry->getClient('google');

        try {
            // the exact class depends on which provider you're using
            /** @var \League\OAuth2\Client\Provider\GoogleUser $user */
            $user = $client->fetchUser();

            // do something with all this new power!
            // e.g. $name = $user->getFirstName();
            dump($user); die;
            // ...
        } catch (IdentityProviderException $e) {
            // something went wrong!
            // probably you should return the reason to the user
            dump($e->getMessage()); die;
        }
    }
}
