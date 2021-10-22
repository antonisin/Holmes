<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use KnpU\OAuth2ClientBundle\Client\Provider\GoogleClient;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class GoogleAuthenticator extends SocialAuthenticator
{
    private ClientRegistry $registry;
    private EntityManagerInterface $manager;
    private RouterInterface $router;

    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $manager, RouterInterface $router)
    {
        $this->registry = $clientRegistry;
        $this->manager  = $manager;
        $this->router   = $router;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse(
            '/connect/', // might be the site, where users choose their oauth provider
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }

    public function supports(Request $request)
    {
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function getCredentials(Request $request)
    {
        return $this->fetchAccessToken($this->getGoogleClient());
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $googleUser = $this->getGoogleClient()->fetchUserFromToken($credentials);
        dump($googleUser);

        $email = $googleUser->getEmail();
        $existUser = $this->manager->getRepository(User::class)->findOneBy([
            'refId' => $googleUser->getId(),
            'type'  => User::GOOGLE_TYPE,
        ]);
        if ($existUser) {
            return $existUser;
        }

        $user = new User();
        $user
            ->setEmail($email)
            ->setFirstName($googleUser->getFirstName())
            ->setLastName($googleUser->getLastName())
            ->setPicture($googleUser->getAvatar())
            ->setRefId($googleUser->getId())
            ->setType(User::GOOGLE_TYPE)
        ;
        $this->manager->persist($user);
        $this->manager->flush();

        return $user;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        return new RedirectResponse($this->router->generate('app_default_index'));
    }

    private function getGoogleClient(): GoogleClient | OAuth2ClientInterface
    {
        return $this->registry->getClient('google');
    }
}