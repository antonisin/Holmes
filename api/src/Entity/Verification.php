<?php

namespace App\Entity;

use App\ThrowException\ModelException;
use Doctrine\ORM\Mapping as ORM;

/**
 * Verification doctrine entity.
 * This class is implemented as doctrine entity model to work and manage verification records. Verification records are
 * used to proof email or phone number. These records are used single time and are deleted for each verification after
 * proof.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.0.0
 */
#[
    ORM\Entity,
    ORM\HasLifecycleCallbacks
]
class Verification extends BaseEntity
{
    public const PHONE_TYPE = 'PHONE_TYPE';
    public const EMAIL_TYPE = 'EMAIL_TYPE';

    public const TYPES = [
        self::PHONE_TYPE,
        self::EMAIL_TYPE,
    ];


    /**
     * Verification code.
     * This property store verification code used to be sent to email or phone number, and also to proof them.
     *
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: false)]
    private int $code;

    /**
     * Number of verification attempts.
     *
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: false, options: ["default" => 0])]
    private int $attempts = 0;

    /**
     * Type of verification.
     * This property contains type of verification. This value needed for different types of data, ex email or phone.
     *
     * @var string
     */
    #[ORM\Column(type: 'string', nullable: false, options: ["default" => Verification::PHONE_TYPE])]
    private string $type;

    /**
     * Related user notification entity instance.
     * This property contain related user notification doctrine entity instance (via database relation).
     *
     * @var UserNotification
     */
    #[
        ORM\OneToOne(inversedBy: 'verification', targetEntity: UserNotification::class),
        ORM\JoinColumn(name: 'notification_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE'),
    ]
    private UserNotification $notification;


    /**
     * Default constructor method.
     * This method is used to pre-define and initialize class in right way.
     *
     * @param UserNotification|null $notification - Notification settings to predefine relation.
     */
    public function __construct(?UserNotification $notification = null)
    {
        if ($notification) {
            $this->notification = $notification;
        }
        $this->generateCode();
    }

    /**
     * Return verification code.
     * This method is used to return verification code used to be sent to email or phone number, and also to proof them.
     *
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * Update verification code.
     * This method is used to update verification code used to be sent to email or phone number, and also to proof them.
     *
     * @param int $code - Verification code used for notification message and for proof.
     *
     * @return Verification
     */
    public function setCode(int $code): Verification
    {
        $this->code = $code;

        return $this;
    }

    /**
     * (Re-)Generate verification code.
     * This method is used to generate or re-generate verification code.
     *
     * @return $this
     */
    public function generateCode(): Verification
    {
        $this->code = rand(100000, 900000);

        return $this;
    }

    /**
     * Return number of verification attempts.
     *
     * @return int
     */
    public function getAttempts(): int
    {
        return $this->attempts;
    }

    /**
     * Update number of verification attempts.
     *
     * @param int $attempts
     *
     * @return Verification
     */
    public function setAttempts(int $attempts): Verification
    {
        $this->attempts = $attempts;

        return $this;
    }

    /**
     * Increment number of attempts.
     *
     * @param int $value - Integer value to be added to current number of attempts.
     *
     * @return $this
     */
    public function addAttempts(int $value = 1): Verification
    {
        $this->attempts += $value;

        return $this;
    }

    /**
     * Return verification type.
     * This method is used to return verification type. For different types of verification type is different.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Update verification type.
     * This method is used to update verification type. For different types of verification type is different.
     *
     * @param string $type - Type of verification.
     *
     * @throws ModelException - Exception if argument $role do not exist in the system.
     */
    public function setType(string $type): Verification
    {
        if (!in_array($type, self::TYPES)) {
            throw new ModelException(sprintf(
                    'Type %s is not valid verification type. Allowed types %s',
                    $type,
                    implode(', ', self::TYPES))
            );
        }

        $this->type = $type;

        return $this;
    }

    /**
     * Return related user notification entity instance.
     * This method is used to return related user notification doctrine entity instance (via database relation).
     *
     * @return UserNotification
     */
    public function getNotification(): UserNotification
    {
        return $this->notification;
    }

    /**
     * Update related user notification entity instance.
     * This method is used to return update user notification doctrine entity instance (via database relation).
     *
     * @param UserNotification $notification - User notification settings entity instance.
     *
     * @return Verification
     */
    public function setNotification(UserNotification $notification): Verification
    {
        $this->notification = $notification;

        return $this;
    }
}