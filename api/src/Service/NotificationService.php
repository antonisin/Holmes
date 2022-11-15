<?php /** @noinspection PhpFullyQualifiedNameUsageInspection */

namespace App\Service;

use App\Lib\NotificationMessage;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\TexterInterface;

/**
 * Base notification service.
 * This class is implemented as symfony service to work and manage notification messages. Class contain all needed
 * methods and properties.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.0.0
 */
class NotificationService
{
    /**
     * Mailer service instance.
     * This property contain mailer service instance used to send email messages with notification content.
     *
     * @var MailerInterface
     */
    private MailerInterface $mailer;

    /**
     * Texter service instance.
     * This property contain texter service instance used to send sms messages with notification content.
     *
     * @var TexterInterface
     */
    private TexterInterface $texter;

    /**
     * Application parameter bag service instance.
     * This property contain instance of parameter bag service used to get application parameters and configs.
     *
     * @var ParameterBagInterface
     */
    private ParameterBagInterface $parameterBag;


    /**
     * NotificationService constructor.
     * This method is used to set up and initialize notification service class.
     *
     * @param TexterInterface       $texter       - Texter service instance used to send sms messages.
     * @param MailerInterface       $mailer       - Mailer service instance used to send email messages.
     * @param ParameterBagInterface $parameterBag - Parameter bag service instance used to get application parameters.
     */
    public function __construct(TexterInterface $texter, MailerInterface $mailer, ParameterBagInterface $parameterBag)
    {
        $this->texter = $texter;
        $this->mailer = $mailer;
        $this->parameterBag = $parameterBag;
    }

    /**
     * Send notification message.
     * This method is used to send notification message via email or sms.
     *
     * @param NotificationMessage $message - Notification message instance to describe content, recipients and other.
     *
     * @return void
     *
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface - In error case on send email.
     * @throws \Symfony\Component\Notifier\Exception\TransportExceptionInterface - In error case on send sms.
     */
    public function send(NotificationMessage $message): void
    {
        if ($message->isSendEmail()) {
            $this->sendEmail($message);
        }
        if ($message->isSendSms()) {
            $this->sendSms($message);
        }
    }

    /**
     * Send email notification message.
     * This method is used to send notification message via email.
     *
     * @param NotificationMessage $message - Notification message instance to describe content, recipients and other.
     *
     * @return void
     *
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface - Exception on sending email.
     */
    public function sendEmail(NotificationMessage $message): void
    {
        $email = new Email();

        $email
            ->from(new Address(
                $message->getFromEmail() ?? $this->parameterBag->get('MAILER_FROM'),
                $message->getFromName() ?? $this->parameterBag->get('MAILER_FROM_NAME')
            ))
            ->to($message->getEmail())
            ->subject($message->getSubject())
            ->text($message->getContent())
            ->html($message->getHtmlContent())
        ;
        $this->mailer->send($email);
    }

    /**
     * Send sms notification message.
     * This method is used to send notification message via sms.
     *
     * @param NotificationMessage $message - Notification message instance to describe content, recipients and other.
     *
     * @return void
     *
     * @throws \Symfony\Component\Notifier\Exception\TransportExceptionInterface
     */
    public function sendSms(NotificationMessage $message): void
    {
        $this->texter->send(new SmsMessage($message->getPhone(), $message->getContent()));
    }
}
