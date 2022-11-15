<?php /** @noinspection PhpFullyQualifiedNameUsageInspection */

namespace App\Service;

use App\Entity\User;
use App\Entity\UserNotification;
use App\Entity\UserNumber;
use App\Entity\Verification;
use App\Lib\Helper;
use App\ThrowException\ModelException;
use App\ThrowException\ServiceException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use App\Lib\NotificationMessage as Message;
use Symfony\Component\Security\Core\Security as SecurityAlias;

/**
 * User notification service class.
 * This class is implemented as symfony service to work with User notification messages and configs.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.0.0
 */
class UserNotificationService
{
    /**
     * Doctrine entity manager instance.
     * This property contain an instance of doctrine entity manager service used to work with database and repositories.
     *
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $manager;

    /**
     * Security service instance.
     * This property contain an instance of security service used to get current user.
     *
     * @var Security
     */
    private Security $security;

    /**
     * UserNotificationService constructor.
     * This method is used to initialize class properties and inject needed services.
     *
     * @var NotificationService
     */
    private NotificationService $notificationService;


    /**
     * UserNotificationService constructor.
     * This method is used to initialize class properties and inject needed services.
     *
     * @param EntityManagerInterface $manager             - Doctrine entity manager instance.
     * @param SecurityAlias          $security            - Security service instance.
     * @param NotificationService    $notificationService - Notification service instance.
     */
    public function __construct(
        EntityManagerInterface $manager,
        Security $security,
        NotificationService $notificationService
    ){
        $this->manager  = $manager;
        $this->security = $security;
        $this->notificationService = $notificationService;
    }

    /**
     * Send notification for found number.
     * This method is used to send notification to user when number is found.
     *
     * @param UserNumber $number - User number entity instance.
     *
     * @return void
     *
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface - If email sending failed.
     * @throws \Symfony\Component\Notifier\Exception\TransportExceptionInterface - If sms sending failed.
     */
    public function sendFound(UserNumber $number): void
    {
        $notification = $number->getUser()->getNotification();

        $message = new Message();
        $message
            ->setPhone($notification->getPhone())
            ->setEmail($notification->getEmail())
            ->setSubject('Personal number - found')
            ->setContent(sprintf('Your personal number %s found', $number->getFormatted()))
        ;
        if ($notification->isEmailEnabled() && $notification->isEmailVerified()) {
            $message->setSendEmail(true);
        }
        if ($notification->isPhoneEnabled() && $notification->isPhoneVerified()) {
            $message->setSendSms(true);
        }

        $this->notificationService->send($message);
    }

    /**
     * Verify if verification code is valid.
     * This method is used to verify and validate verification code. If code is valid, method will remove verification
     * record from the system and will update state of verified field (email, sms) in user notification settings. $user
     * argument can be null if verification needed for login via email or sms.
     *
     * @param int       $code - Verification Code.
     * @param User|null $user - User entity instance. Can be null for login vial email or sms.
     *
     * @return bool
     *
     * @throws ServiceException
     */
    public function verify(int $code, ?User $user = null): bool
    {
        if (is_null($user)) {
            $user = $this->security->getUser();
        }

        $notification = $user->getNotification();
        $verification = $notification->getVerification();
        if (is_null($verification)) {
            throw new ServiceException('No verification to be sent.');
        }

        if ($verification->getCode() === $code) {
            if (Verification::PHONE_TYPE === $verification->getType()) {
                $notification->setPhoneVerified(true);
            } else {
                $notification->setEmailVerified(true);
            }
            $this->manager->remove($verification);
            $this->manager->flush();

            return true;
        }

        return false;
    }

    /**
     * Send user verification message.
     * This method is used to send verification code message to user.
     *
     * @param string $type - Verification type (email or sms).
     *
     * @return void
     *
     * @throws ServiceException - Exception on maxim attempts count.
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface - If email sending failed.
     * @throws \Symfony\Component\Notifier\Exception\TransportExceptionInterface - If sms sending failed.
     * @throws ModelException - Exception on model validation error (for ex. invalid type).
     */
    public function sendVerification(string $type): void
    {
        $this->processVerification($this->security->getUser(), $type);
    }

    /**
     * Send user login verification message.
     * This method is used to send verification code message to user for login via email or sms.
     *
     * @param User   $user - User entity instance. Can be null for login vial email or sms.
     * @param string $type - Verification type (email or sms).
     *
     * @return void
     *
     * @throws ServiceException - Exception on maxim attempts count.
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface - If email sending failed.
     * @throws \Symfony\Component\Notifier\Exception\TransportExceptionInterface - If sms sending failed.
     * @throws ModelException - Exception on model validation error (for ex. invalid type).
     */
    public function sendLoginVerification(User $user, string $type): void
    {
        $this->processVerification($user, $type);
    }

    /**
     * Process sending verification message.
     * This method is used to process sending verification code message to user.
     *
     * @param User|null $user - User entity instance. Can be null for login vial email or sms.
     * @param string    $type - Verification type (email or sms).
     *
     * @return void
     *
     * @throws ServiceException - Exception on maxim attempts count.
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface - If email sending failed.
     * @throws \Symfony\Component\Notifier\Exception\TransportExceptionInterface - If sms sending failed.
     * @throws ModelException - Exception on model validation error (for ex. invalid type).
     */
    private function processVerification(?User $user, string $type): void
    {
        $notification = $user->getNotification();
        $verification = $notification->getVerification();

        if (is_null($verification)) {
            $verification = self::generateVerification($notification, $type);
            $notification->setVerification($verification);
        } else {
            $verification->generateCode();
        }

        if (Verification::MAX_ATTEMPTS <= $verification->getAttempts()) {
            if (Helper::passHours($verification->getUpdatedAt()) < 1) {
                throw new ServiceException('Reached limit of attempts for verification.');
            }
            $verification->setAttempts(0);
        }

        $verification = $notification->getVerification();
        $verification->addAttempts();
        $this->manager->persist($verification);
        $this->manager->flush();


        $message = new Message();
        $message
            ->setPhone($notification->getPhone())
            ->setEmail($notification->getEmail())
            ->setSubject('Verification Code')
            ->setContent(sprintf('Verification Code: %d', $verification->getCode()))
        ;
        if (Verification::PHONE_TYPE === $verification->getType()) {
            $message->setSendSms(true);
        } else if (Verification::EMAIL_TYPE === $verification->getType()) {
            $message->setSendEmail(true);
        }

        $this->notificationService->send($message);
    }

    /**
     * Generate clean/new verification entity.
     * This method is used to generate clean/new verification entity.
     *
     * @param UserNotification $notification - User notification entity instance.
     * @param string           $type         - Verification type (email or sms).
     *
     * @return Verification
     *
     * @throws ModelException - Exception on model validation error (for ex. invalid type).
     */
    private function generateVerification(UserNotification $notification, string $type): Verification
    {
        $verification = new Verification();
        $verification
            ->setType($type)
            ->setNotification($notification)
            ->generateCode()
        ;

        return $verification;
    }
}