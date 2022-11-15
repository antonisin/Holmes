<?php /** @noinspection PhpUnused */

namespace App\Lib;

/**
 * Notification message class.
 * This class is implemented as model to work with notification service. Class contain all needed properties and values
 * useful on sending notification via email or phone.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.0.0
 */
class NotificationMessage
{
    /**
     * Email address.
     * This property contain email address used to send notification. This value allowed to be null in case when email
     * is not used.
     *
     * @var string|null
     */
    private ?string $email = null;

    /**
     * Send email flag state.
     * This property shown if we need to send notification via email or not.
     *
     * @var bool
     */
    private bool $sendEmail = false;

    /**
     * Phone number.
     * This property contain phone number used to send notification. This value allowed to be null in case when phone
     *
     * @var int|null
     */
    private ?int $phone = null;

    /**
     * Send phone flag state.
     * This property shown if we need to send notification via phone or not.
     *
     * @var bool
     */
    private bool $sendSms = false;

    /**
     * Notification subject.
     * This property contain subject value. Subject is used on email notification message.
     *
     * @var string
     */
    private string $subject;

    /**
     * Notification content.
     * This property contain notification content. Content is used usually in sms notification messages. In case when
     * notification do not have $htmlContent property, will be used content for email notification also.
     * @var string
     */
    private string $content;

    /**
     * Notification html content.
     * This property contain html notification content. HTML content is used usually in email notification messages. In
     * case when notification do not have $htmlContent property, will be used $content value.
     *
     * @var string|null
     */
    private ?string $htmlContent = null;

    /**
     * Email from address.
     * This property contain sender from email address.
     *
     * @var string|null
     */
    private ?string $fromEmail = null;

    /**
     * Email from name.
     * This property contain sender name.
     *
     * @var string|null
     */
    private ?string $fromName = null;


    /**
     * Return email address.
     * This method return email address used to send notification. This value allowed to be null in case when email is
     * not used.
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Update email address.
     * This method update email address used to send notification. This value allowed to be null in case when email is
     * not used.
     *
     * @param string|null $email - Email address used for notification.
     *
     * @return $this
     */
    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Return send email flag state.
     * This method is used to identify if we need to send email notification or not.
     *
     * @return bool
     */
    public function isSendEmail(): bool
    {
        return $this->sendEmail;
    }

    /**
     * Update send email flag state.
     * This method is used to update send email flag state. If true, will be sent email notification.
     *
     * @param bool $sendEmail - Send email flag state.
     *
     * @return $this
     */
    public function setSendEmail(bool $sendEmail): self
    {
        $this->sendEmail = $sendEmail;

        return $this;
    }

    /**
     * Return phone number.
     * This method return phone number used to send notification. This value allowed to be null in case when phone is
     * not used.
     *
     * @return int|null
     */
    public function getPhone(): ?int
    {
        return $this->phone;
    }

    /**
     * Update phone number.
     * This method update phone number used to send notification. This value allowed to be null in case when phone is
     * not used.
     *
     * @param int|null $phone - Phone number used for notification.
     *
     * @return $this
     */
    public function setPhone(?int $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Return send phone flag state.
     * This method is used to identify if we need to send phone notification or not.
     *
     * @return bool
     */
    public function isSendSms(): bool
    {
        return $this->sendSms;
    }

    /**
     * Update send phone flag state.
     * This method is used to update send phone flag state. If true, will be sent phone notification.
     *
     * @param bool $sendSms - Send phone flag state.
     *
     * @return $this
     */
    public function setSendSms(bool $sendSms): self
    {
        $this->sendSms = $sendSms;

        return $this;
    }

    /**
     * Return notification subject.
     * This method return notification subject. Subject is used on email notification message.
     *
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * Update notification subject.
     * This method update notification subject. Subject is used on email notification message.
     *
     * @param string $subject - Notification subject.
     *
     * @return $this
     */
    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Return notification content.
     * This method return notification content. Content is used usually in sms notification messages. In case when
     * notification do not have $htmlContent property, will be used content for email notification also.
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Update notification content.
     * This method update notification content. Content is used usually in sms notification messages. In case when
     * notification do not have $htmlContent property, will be used content for email notification also.
     *
     * @param string $content - Notification content.
     *
     * @return $this
     */
    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Return notification html content.
     * This method return notification html content. HTML content is used usually in email notification messages. In
     * case when notification do not have $htmlContent property, will be used $content value.
     *
     * @return string
     */
    public function getHtmlContent(): string
    {
        if (!$this->htmlContent) {
            return $this->content;
        }

        return $this->htmlContent;
    }

    /**
     * Update notification html content.
     * This method update notification html content. HTML content is used usually in email notification messages. In
     * case when notification do not have $htmlContent property, will be used $content value.
     *
     * @param string $htmlContent - Notification html content.
     *
     * @return $this
     */
    public function setHtmlContent(string $htmlContent): self
    {
        $this->htmlContent = $htmlContent;

        return $this;
    }

    /**
     * Return email from address.
     * This method return email from address.
     *
     * @return string|null
     */
    public function getFromEmail(): ?string
    {
        return $this->fromEmail;
    }

    /**
     * Update email from address.
     * This method update email from address.
     *
     * @param string $fromEmail - Email from address.
     *
     * @return $this
     */
    public function setFromEmail(string $fromEmail): self
    {
        $this->fromEmail = $fromEmail;

        return $this;
    }

    /**
     * Return email from name.
     * This method return email from name.
     *
     * @return string|null
     */
    public function getFromName(): ?string
    {
        return $this->fromName;
    }

    /**
     * Update email from name.
     * This method update email from name.
     *
     * @param string $fromName - Email from name.
     *
     * @return $this
     */
    public function setFromName(string $fromName): self
    {
        $this->fromName = $fromName;

        return $this;
    }
}