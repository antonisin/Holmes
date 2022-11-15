<?php

namespace App\Security;

use App\Entity\User;
use App\Entity\Verification;
use App\Lib\Helper;
use App\ThrowException\AppException;
use App\ThrowException\ModelException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * Application Code Authenticator.
 * This class is used to authenticate user by code. User can auth using verification code received via email or sms
 * message.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.0.0
 */
class CodeAuthenticator extends AbstractAuthenticator
{
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
     * CodeAuthenticator constructor.
     * This method is used to initialize class properties and inject needed services.
     *
     * @param EntityManagerInterface $manager - Doctrine entity manager instance.
     * @param RouterInterface        $router  - Router service instance. Needed for redirect and url generate.
     */
    public function __construct(EntityManagerInterface $manager, RouterInterface $router)
    {
        $this->manager = $manager;
        $this->router = $router;
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request): ?bool
    {
        return 'login_verify' === $request->attributes->get('_route')
            && ($request->request->has('code') || $request->query->has('code'))
        ;
    }

    /**
     * {@inheritDoc}
     *
     * @throws ModelException - Exception in case when role is invalid.
     * @throws AppException - Exception if verification code is not valid.
     */
    public function authenticate(Request $request): Passport
    {
        $code = Helper::normalizeString($request->get('code'));
        /** @var Verification $verification */
        $verification = $this->manager->getRepository(Verification::class)->findOneBy(['code' => $code]);
        if (!$verification) {
            throw new AppException('Verification code is not valid');
        }

        $user = $verification->getNotification()->getUser();
        $user->removeRole(User::ROLE_TEMP_USER);

        if (Verification::PHONE_TYPE === $verification->getType()) {
            $user->getNotification()->setPhoneVerified(true);
        } else {
            $user->getNotification()->setEmailVerified(true);
        }

        $this->manager->persist($user);
        $this->manager->remove($verification);
        $this->manager->flush();


        return new SelfValidatingPassport(
            new UserBadge($user->getId(), function () use ($user) {
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