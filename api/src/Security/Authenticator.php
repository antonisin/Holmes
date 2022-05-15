<?php

namespace App\Security;

use App\Entity\User;
use App\Security\Extractor\FacebookExtractor;
use App\Security\Extractor\GoogleExtractor;
use App\Security\Extractor\OKExtractor;
use App\Security\Extractor\VKExtractor;
use App\ThrowException\AppException;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * Application Authenticator class/service/
 * This class is implemented as system authenticator to auth users. The main way to authenticate user are social
 * networks. Class is used as base authentication symfony way.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.0.0
 */
class Authenticator extends OAuth2Authenticator
{
    /**
     * Client Registry.
     * This property contains instance of client registry used to get social network client for authentication.
     *
     * @var ClientRegistry
     */
    private ClientRegistry $clientRegistry;

    /**
     * Doctrine Entity manager instance.
     * This property contain an instance of doctrine entity manager service used to work with database and repositories.
     *
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $manager;

    /**
     * Router service instance.
     * This property contain router service instance used to generate urls and paths. For example to redirect user on
     * successful authentication.
     *
     * @var RouterInterface
     */
    private RouterInterface $router;


    /**
     * Base class constructor method.
     * This method is used to extract arguments services and setup class before usage.
     *
     * @param ClientRegistry         $clientRegistry - Client registry used to get social network client for auth.
     * @param EntityManagerInterface $manager        - Doctrine entity service to work with database.
     * @param RouterInterface        $router         - Router service instance to generate urls and paths.
     */
    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $manager, RouterInterface $router)
    {
        $this->clientRegistry = $clientRegistry;
        $this->manager = $manager;
        $this->router  = $router;
    }

    /**
     * {@inheritDoc}
     */
    public function supports(Request $request): ?bool
    {
        return preg_match('/^connect_check_\w+$/', $request->attributes->get('_route')) === 1;
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate(Request $request): Passport
    {
        $type   = str_replace('connect_check_', '', $request->attributes->get('_route'));
        $client = $this->clientRegistry->getClient($type);
        $token  = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($token->getToken(), function() use ($token, $client, $type) {
                $authUser = $client->fetchUserFromToken($token);

                /** @TODO Updated findOneBy condition to avoid same id in different social networks. */
                $existUser = $this->manager->getRepository(User::class)->findOneBy([
                    'refId' => $authUser->getId(),
                ]);
                if ($existUser) {
                    return $existUser;
                }

                $user = match ($type) {
                    'google'        => GoogleExtractor::getUser($authUser),
                    'facebook'      => FacebookExtractor::getUser($authUser),
                    'vkontakte'     => VKExtractor::getUser($authUser),
                    'odnoklassniki' => OKExtractor::getUser($authUser),
                    default         => throw new AppException(sprintf('Unknown social network and extractor %d', $type)),
                };

                if ($user->getEmail()) {
                    $user->getNotification()
                        ->setEmail($user->getEmail())
                        ->setEmailVerified(true)
                    ;
                }
                $this->manager->persist($user);
                $this->manager->flush();

                return $user;
            })
        );
    }

    /**
     * {@inheritDoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->router->generate('dashboard'));
    }

    /**
     * {@inheritDoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }
}