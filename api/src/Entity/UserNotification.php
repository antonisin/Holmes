<?php

namespace App\Entity;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;

/**
 * User notification doctrine entity model.
 * This class is implemented as document entity model to work with user notification settings. These settings are used
 * for notification via email and phone.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.1.0
 */
#[
    ORM\Entity,
    ORM\HasLifecycleCallbacks
]
class UserNotification extends BaseEntity
{
    /**
     * Phone verified or not.
     * This property show if phone was verified or not.
     *
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    private bool $phoneVerified = false;

    /**
     * Email verified or not.
     * This property shown if email was verified or not.
     *
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    private bool $emailVerified = false;

    /**
     * User's phone for notification.
     *
     * @var int|null
     */
    #[ORM\Column(type: 'bigint', nullable: true)]
    private int|null $phone = null;

    /**
     * User email for notification.
     *
     * @var string|null
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private string|null $email = null;

    /**
     * Enabled or not phone notification.
     * This setting is used to enable or disable phone notification.
     *
     * @var bool
     */
    #[ORM\Column(type: 'boolean', nullable: false, options: ["default" => false])]
    private bool $phoneEnabled = false;

    /**
     * Enabled or not email notification.
     * This setting is used to enable or disable email notification.
     *
     * @var bool
     */
    #[ORM\Column(type: 'boolean', nullable: false, options: ["default" => false])]
    private bool $emailEnabled = false;

    /**
     * Related user entity model.
     * This property contain related (based on database relation) user entity model (own).
     *
     * @var User
     */
    #[
        ORM\OneToOne(inversedBy: 'notification', targetEntity: User::class),
        ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE'),
    ]
    private User $user;

    /**
     * Related Verification entity model.
     * This property contain related (based on database relation) verification entity model. Verification records are
     * generated when need to verify user's phone or email. In case when email/phone was already verified or
     * verification was not requested, this property will contain null value.
     *
     * @var Verification|null
     */
    #[ORM\OneToOne(mappedBy: 'notification', targetEntity: Verification::class, cascade: ['remove'], fetch: 'EAGER')]
    private null|Verification $verification = null;


    /**
     * Update user entity model.
     * This method is used to update related (based on database relation) user entity model (own).
     *
     * @param User $user - Notification settings owner user.
     *
     * @return $this
     */
    public function setUser(User $user): UserNotification
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Return user entity model.
     * This method is used to return related (based on database relation) user entity model (own).
     *
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Check if phone was verified.
     *
     * @return bool
     */
    public function isPhoneVerified(): bool
    {
        return $this->phoneVerified;
    }

    /**
     * Update phone verification state.
     *
     * @param bool $phoneVerified - New phone verification state.
     *
     * @return UserNotification
     */
    public function setPhoneVerified(bool $phoneVerified): UserNotification
    {
        $this->phoneVerified = $phoneVerified;

        return $this;
    }

    /**
     * Check if email was verified.
     *
     * @return bool
     */
    public function isEmailVerified(): bool
    {
        return $this->emailVerified;
    }

    /**
     * Update email verification state.
     *
     * @param bool $emailVerified - New email verification state.
     *
     * @return UserNotification
     */
    public function setEmailVerified(bool $emailVerified): UserNotification
    {
        $this->emailVerified = $emailVerified;

        return $this;
    }

    /**
     * Return related Verification entity model.
     * This method is used to return related (based on database relation) verification entity model. Verification records
     * are generated when need to verify user's phone or email. In case when email/phone was already verified or
     * verification was not requested, this method will return null value.
     *
     * @return Verification|null
     */
    public function getVerification(): ?Verification
    {
        return $this->verification;
    }

    /**
     * Update related Verification entity model.
     * This method is used to update related (based on database relation) verification entity model. Verification records
     * are generated when need to verify user's phone or email. In case when email/phone was already verified or
     * verification was not requested, method must be used to set null value.
     *
     * @param Verification|null $verification - Verification entity instance used to check email/phone.
     *
     * @return UserNotification
     */
    public function setVerification(?Verification $verification): UserNotification
    {
        $this->verification = $verification;

        return $this;
    }

    /**
     * Return phone number for notification.
     *
     * @return int|null
     */
    public function getPhone(): ?int
    {
        return $this->phone;
    }

    /**
     * Update phone number for verification.
     *
     * @param int|null $phone - User's phone number used for notification messages.
     *
     * @return $this
     */
    public function setPhone(?int $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Return email for notifications.
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Update email for notification.
     *
     * @param string|null $email - User's email used for notification messages.
     *
     * @return $this
     */
    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Check if phone notification is enabled.
     * This method is used to check if phone notification is enabled.
     *
     * @return bool
     */
    public function isPhoneEnabled(): bool
    {
        return $this->phoneEnabled;
    }

    /**
     * Update phone notification state.
     * This method is used to update phone notification state to send or not notification messages.
     *
     * @param bool $phoneEnabled - State of phone notification.
     *
     * @return $this
     */
    public function setPhoneEnabled(bool $phoneEnabled): self
    {
        $this->phoneEnabled = $phoneEnabled;

        return $this;
    }

    /**
     * Check if email notification is enabled.
     * This method is used to check if email notification is enabled.
     *
     * @return bool
     */
    public function isEmailEnabled(): bool
    {
        return $this->emailEnabled;
    }

    /**
     * Update email notification state.
     * This method is used to update email notification state to send or not notification messages.
     *
     * @param bool $emailEnabled - State of email notification.
     *
     * @return $this
     */
    public function setEmailEnabled(bool $emailEnabled): self
    {
        $this->emailEnabled = $emailEnabled;

        return $this;
    }

    /**
     * Method called on every record update process.
     *
     * @param PreUpdateEventArgs $opt - Options contain old and new entity properties values.
     *
     * @return void
     */
    #[ORM\PreUpdate]
    public function preUpdate(PreUpdateEventArgs $opt): void
    {
        parent::preUpdate($opt);

        /** Check if phone and email was updated. If yes, verification boolean status need to be updated to false. */
        if ($opt->hasChangedField('phone') && (int) $opt->getNewValue('phone') !== (int) $opt->getOldValue('phone')) {
            $this->setPhoneVerified(false);
        }

        if ($opt->hasChangedField('email') && $opt->getNewValue('email') !== $opt->getOldValue('email')) {
            $this->setEmailVerified(false);
        }
    }
}