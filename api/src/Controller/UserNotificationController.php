<?php /** @noinspection PhpFullyQualifiedNameUsageInspection */

namespace App\Controller;

use App\Entity\UserNotification;
use App\Entity\Verification;
use App\Lib\Helper;
use App\Service\UserNotificationService;
use App\ThrowException\ModelException;
use App\ThrowException\ServiceException;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * User Notification Controller.
 * This class is implemented as symfony controller to work with user notification settings and data. Also, class is used
 * for email and phone verification (notification contacts/ways verification).
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.1.0
 */
#[Route('/notifications/')]
class UserNotificationController extends AbstractFOSRestController
{
    /**
     * Update user notification settings.
     * This method is used to update user notification settings (email, phone number). In case when request contain
     * verification code param, this method will call verification service.
     *
     * @param Request                 $request - Request instance to extract needed information (like params).
     * @param EntityManagerInterface  $manager - Doctrine entity manager instance to work with database.
     * @param UserNotificationService $service - User notification service used for notifications and verification.
     *
     * @return RedirectResponse - Redirect response on any types of requests.
     *
     * @throws ServiceException - UserNotificationService::class may throw exception.
     */
    #[Route("", name: 'app_user_notification_post', methods: [ Request::METHOD_POST ])]
    public function post(Request $request, EntityManagerInterface $manager, UserNotificationService $service): RedirectResponse
    {
        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        $model = $this->getUser()->getNotification();
        $code  = Helper::normalizeString($request->get('verificationCode', false));

        if ($code) {
            $service->verify($code);

            return $this->redirectToRoute('app_user_settings');
        } elseif ($request->request->has('verificationCode')) {
            $this->addFlash('error', 'Invalid verification code');
        }

        $phone = Helper::normalizePhone($request->get('phone'));
        /** @var UserNotification $model */
        $model
            ->setPhone($phone === 0 ? null : $phone)
            ->setPhoneEnabled($request->get('phoneEnabled', false))
            ->setEmail($request->get('email', ''))
            ->setEmailEnabled($request->get('emailEnabled', false))
        ;

        $manager->persist($model);
        $manager->flush();

        return $this->redirectToRoute('app_user_settings');
    }

    /**
     * Send verification code.
     * This method is used to send verification code to email or phone number and redirect user back to settings page.
     *
     * @param UserNotificationService $service - User notification service used for notifications and verification.
     * @param string                  $_route  - Route name used for request. Value needed to identify type of verify.
     *
     * @return RedirectResponse - Redirect response on any types of requests.
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface - If email sending failed.
     * @throws \Symfony\Component\Notifier\Exception\TransportExceptionInterface - If sms sending failed.
     * @throws ModelException - Exception on model validation error (for ex. invalid type).
     */
    #[
        Route("verify/email", name: 'app_user_notification_verify_email', methods: [ Request::METHOD_GET ]),
        Route("verify/phone", name: 'app_user_notification_verify_phone', methods: [ Request::METHOD_GET ]),
    ]
    public function verify(UserNotificationService $service, string $_route): RedirectResponse
    {
        $type = $_route === 'app_user_notification_verify_email' ? Verification::EMAIL_TYPE : Verification::PHONE_TYPE;
        try {
            $service->sendVerification($type);
        } catch (ServiceException $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToRoute('app_user_settings');
    }
}