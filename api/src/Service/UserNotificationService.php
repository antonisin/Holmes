<?php

namespace App\Service;

use App\Entity\UserNotification;
use App\Entity\UserNumber;
use App\Entity\Verification;
use App\ThrowException\ServiceException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\TexterInterface;

class UserNotificationService
{
    private UserNotification $notification;
    private TexterInterface  $texter;
    private EntityManagerInterface $manager;
    private MailerInterface $mailer;


    public function __construct(TexterInterface $texter, EntityManagerInterface $manager, MailerInterface $mailer)
    {
        $this->texter = $texter;
        $this->manager = $manager;
        $this->mailer = $mailer;
    }

    public function generateVerification(string $type = Verification::PHONE_TYPE): self
    {
        if (is_null($this->notification->getVerification())) {
            $this->notification->setVerification(new Verification($this->notification));

        }
        $verification = $this->notification->getVerification();
        $verification
            ->setType($type)
            ->addAttempts(1)
            ->generateCode()
        ;

        if ($verification->getAttempts() > 3) {
            throw new ServiceException('Reached limit of attempts for verification.');
        }

        $this->manager->persist($verification);
        $this->manager->flush();

        return $this;
    }

    public function sendVerification()
    {
        $verification = $this->notification->getVerification();
        if (is_null($verification)) {
            throw new ServiceException('No verification to be sent.');
        }

        if (Verification::PHONE_TYPE === $verification->getType()) {
            if (empty($this->notification->getPhone())) {
                throw new ServiceException('No phone to send verification.');
            }
            $this->texter->send(new SmsMessage(
                $this->notification->getPhone(),
                sprintf('Verification Code: %d', $verification->getCode())
            ));
        }

        if (Verification::EMAIL_TYPE === $verification->getType()) {
            if (empty($this->notification->getEmail())) {
                throw new ServiceException('No email to send verification.');
            }
            $email = (new Email())
                ->from(new Address('verify@aflaro.com', 'AlfaRo'))
                ->to($this->notification->getEmail())
                ->subject('Verification Code')
                ->html(sprintf("<p>Yor Verification Code is: %d </p>", $verification->getCode()));
            ;

            $this->mailer->send($email);
        }
    }

    public function verify(int $code)
    {
        $verification = $this->notification->getVerification();
        if (is_null($verification)) {
            throw new ServiceException('No verification to be sent.');
        }

        if ($verification->getCode() === $code) {
            if (Verification::PHONE_TYPE === $verification->getType()) {
                $this->notification->setPhoneVerified(true);
            } else {
                $this->notification->setEmailVerified(true);
            }
            $this->manager->remove($verification);
            $this->manager->flush();

            return true;
        }

        return false;
    }

    public function sendNotification(UserNumber $number)
    {
        $msg = sprintf('Your personal number %s found', $number->getFormatted());
        if ($this->notification->isPhoneVerified()) {
            $this->texter->send(new SmsMessage($this->notification->getPhone(), $msg));
        }
        if ($this->notification->isEmailVerified()) {
            $email = (new Email())
                ->from(new Address('notify@aflaro.com', 'AlfaRo'))
                ->to($this->notification->getEmail())
                ->subject('Verification Code')
                ->html(sprintf("<p>%s</p>", $msg));
            ;

            $this->mailer->send($email);
        }
    }

    public function setNotification(UserNotification $notification): self
    {
        $this->notification = $notification;

        return $this;
    }
}