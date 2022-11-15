<?php /** @noinspection PhpFullyQualifiedNameUsageInspection */

namespace App\Controller;

use App\Entity\User;
use App\Entity\Verification;
use App\Lib\Helper;
use App\Lib\PetName;
use App\Service\UserNotificationService;
use App\ThrowException\ModelException;
use App\ThrowException\ServiceException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Login controller class.
 * This class is implemented as symfony controller to rule login process, render login form and process social network
 * authentication. Need to keep in mind that real authentication is managed by service Authentication class.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.1.0
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
     * Doctrine Entity Manager.
     * This property contain Doctrine Entity Manager service instance. Manager is used to work with database and
     * repositories (update, delete, find, insert and others).
     *
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $manager;


    /**
     * Login controller constructor.
     * This method is used to initialize controller properties and inject dependencies.
     *
     * @param EntityManagerInterface $manager - Doctrine entity manager to work with database.
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

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
        return $this->render('/login/login.html.twig');
    }

    /**
     * Logout path.
     * This method do not any actions and used just to define path of logout process.
     *
     * @return void
     */
    #[Route("/logout", name: 'logout')]
    public function logoutAction(): void
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
    public function connectCheckAction(): void
    {
    }

    /**
     * Login with email and verification.
     * This method is used to render login by email page and auth with verification code.
     *
     * @return Response
     */
    #[Route("/login/email", name: "login_email")]
    public function emailLogin(): Response
    {
        return $this->render('login/email.html.twig');
    }

    /**
     * Send email verification code.
     * This method is used to send verification code to user email.
     *
     * @param Request $request - User request instance.
     * @param UserNotificationService $service - User notification service instance.
     *
     * @return Response
     *
     * @throws ModelException - Entity model exception. (For example if role is not valid).
     * @throws NonUniqueResultException - If user is not uniq.
     * @throws ServiceException - Exception on maxim attempts count.
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface - If email sending failed.
     * @throws \Symfony\Component\Notifier\Exception\TransportExceptionInterface - If sms sending failed.
     */
    #[Route("/login/email/verify", name: "login_email_verify", methods: [Request::METHOD_POST])]
    public function emailVerify(Request $request, UserNotificationService $service): Response
    {
        $email = $request->request->get('email');
        if (empty($email)) {
            $this->addFlash('error', 'Email address must be valid and present');

            return $this->redirectToRoute('login_email');
        }

        $user = $this->findUser($email, User::ROLE_EMAIL);
        $service->sendLoginVerification($user, Verification::EMAIL_TYPE);
        $this->addFlash('success', sprintf('Verification code sent to %s', $email));

        return $this->redirectToRoute('login_verify');
    }

    /**
     * Login with phone and verification.
     * This method is used to render login by phone page and auth with verification code.
     *
     * @return Response
     */
    #[Route("/login/phone", name: "login_phone")]
    public function phoneLogin(): Response
    {
        return $this->render('login/phone.html.twig');
    }

    /**
     * Send phone verification code.
     * This method is used to send verification code to user phone.
     *
     * @param Request                 $request - User request instance.
     * @param UserNotificationService $service - User notification service instance.
     *
     * @return Response
     *
     * @throws ModelException - Entity model exception. (For example if role is not valid).
     * @throws NonUniqueResultException - If user is not uniq.
     * @throws ServiceException - Exception on maxim attempts count.
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface - If email sending failed.
     * @throws \Symfony\Component\Notifier\Exception\TransportExceptionInterface - If sms sending failed.
     */
    #[Route("/login/phone/verify", name: "login_phone_verify", methods: [Request::METHOD_POST])]
    public function phoneVerify(Request $request, UserNotificationService $service): Response
    {
        $phone = Helper::normalizePhone($request->request->get('phone'));
        if (0 === $phone) {
            $this->addFlash('error', 'Phone number must be valid and present');

            return $this->redirectToRoute('login_phone');
        }

        $user = $this->findUser($phone, User::ROLE_PHONE);
        $service->sendLoginVerification($user, Verification::PHONE_TYPE);
        $this->addFlash('success', sprintf('Verification code sent to %s', $phone));

        return $this->redirectToRoute('login_verify');
    }

    /**
     * Render verification page.
     * This method is used to render verification page to enter/fill/pass received code.
     *
     * @return Response
     */
    #[Route("/login/verify", name: "login_verify", methods: [Request::METHOD_POST, Request::METHOD_GET])]
    public function verify(): Response
    {
        return $this->render('login/verify.html.twig');
    }

    /**
     * Find existing user or create new one if not exists.
     * This method is used to get user for login by email or phone. If user with required role exist, method will
     * return it. In case user not exist, method will create new one and return it.
     *
     * @param string $refId - User reference id (email or phone).
     * @param string $role - User role.
     *
     * @return User
     *
     * @throws ModelException - Exception on user entity validation error (for ex. invalid role).
     * @throws NonUniqueResultException - Exception on non unique result.
     * @throws \Exception - Exception on get new avatar url.
     */
    private function findUser(string $refId, string $role): User
    {
        /** Check using query builder for existing user with required role. */
        $qb = $this->manager->getRepository(User::class)->createQueryBuilder('u');
        $qb
            ->where($qb->expr()->like('u.roles', ':role'))
            ->setParameter('role', sprintf('%%%s%%', $role))
            ->andWhere($qb->expr()->eq('u.refId', ':refId'))
            ->setParameter('refId', $refId)
        ;

        $user = $qb->getQuery()->getOneOrNullResult();
        if ($user) {
            return $user;
        }

        /** Create new user if not exist with required role. */
        $user = new User();
        $user
            ->setRefId($refId)
            ->setFirstName(PetName::randAdvert())
            ->setLastName(PetName::randName())
            ->addRole(User::ROLE_TEMP_USER, $role)
            ->setPicture(Helper::randAvatar())
        ;

        if (User::ROLE_EMAIL === $role) {
            $user
                ->getNotification()
                ->setEmail($refId)
                ->setEmailEnabled(true)
            ;
        } elseif (User::ROLE_PHONE === $role) {
            $user
                ->getNotification()
                ->setPhone($refId)
                ->setPhoneEnabled(true)
            ;
        }

        $this->manager->persist($user);
        $this->manager->flush();

        return $user;
    }
}
