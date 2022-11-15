<?php /** @noinspection PhpUnused */

namespace App\Entity;

use App\Repository\UserRepository;
use App\ThrowException\ModelException;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User doctrine entity model.
 * This class is implemented as doctrine entity model to work and manage users in the system.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.1.0
 */
#[
    ORM\Entity(repositoryClass: UserRepository::class),
    ORM\HasLifecycleCallbacks
]
class User extends BaseEntity implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const ROLE_USER     = 'ROLE_USER';
    public const ROLE_ADMIN    = 'ROLE_ADMIN';
    public const ROLE_GOOGLE   = 'ROLE_GOOGLE';
    public const ROLE_FACEBOOK = 'ROLE_FACEBOOK';
    public const ROLE_VK       = 'ROLE_VK';
    public const ROLE_OK       = 'ROLE_OK';
    public const ROLE_EMAIL    = 'ROLE_EMAIL';
    public const ROLE_PHONE    = 'ROLE_PHONE';
    public const ROLE_TEMP_USER = 'ROLE_TEMP_USER';

    public const ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_GOOGLE,
        self::ROLE_USER,
        self::ROLE_FACEBOOK,
        self::ROLE_VK,
        self::ROLE_OK,
        self::ROLE_EMAIL,
        self::ROLE_PHONE,
        self::ROLE_TEMP_USER,
    ];


    /**
     * User's first name.
     *
     * @var string
     */
    #[ORM\Column(type: 'string')]
    private string $firstName;

    /**
     * User's last name.
     *
     * @var string
     */
    #[ORM\Column(type: 'string')]
    private string $lastName;

    /**
     * Link to user's avatar/icon/picture.
     *
     * @var string|null
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private string|null $picture = null;

    /**
     * Reference id.
     * This property contain reference id from social network on authentication process.
     *
     * @var string
     */
    #[ORM\Column(type: 'string')]
    private string $refId;

    /**
     * Array of roles.
     * This property contain an array of all user's roles.
     *
     * @var array
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private array $roles = [self::ROLE_USER];

    /**
     * Relation with notification settings.
     * This property contain instance of user notification settings entity based on database relation.
     *
     * @var UserNotification
     */
    #[ORM\OneToOne(mappedBy: 'user', targetEntity: UserNotification::class, cascade: ['persist'])]
    private UserNotification $notification;


    /**
     * Base constructor method.
     * This method will prepare default properties and sensitive data.
     */
    public function __construct()
    {
        if (empty($this->notification)) {
            $this->notification = new UserNotification();
            $this->notification->setUser($this);
        }
    }

    /**
     * Return user's first name.
     *
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * Update user's first name.
     *
     * @param string $firstName - User first name value.
     *
     * @return User
     */
    public function setFirstName(string $firstName): User
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Return user's last name.
     *
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * Update user's last name.
     *
     * @param string $lastName - User's last name.
     *
     * @return User
     */
    public function setLastName(string $lastName): User
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Return user's avatar/picture/icon url.
     *
     * @return string|null
     */
    public function getPicture(): ?string
    {
        return $this->picture;
    }

    /**
     * Update user's avatar/picture/icon url.
     *
     * @param string|null $picture - Url to user's avatar/picture/icon.
     *
     * @return User
     */
    public function setPicture(?string $picture): User
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * Return reference id.
     * This method is used to return reference id from social network on authentication process.
     *
     * @return string
     */
    public function getRefId(): string
    {
        return $this->refId;
    }

    /**
     * Update reference id.
     * This method is used to update reference id from social network on authentication process.
     *
     * @param string $refId - Reference id value from social network authentication.
     */
    public function setRefId(string $refId): self
    {
        $this->refId = $refId;

        return $this;
    }

    /**
     * Return user's roles.
     * This method is used to return array of all user's roles.
     *
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * Add new user's role.
     * This method is used to add new user's role.
     *
     * @param array<string> $roles - Role value to be added
     *
     * @return self
     *
     * @throws ModelException - Model exception on invalid role.
     */
    public function addRole(...$roles): self
    {
        array_map(function ($role) {
            self::validateRole($role);
        }, $roles);

        $this->roles = array_merge($this->roles, $roles);

        return $this;
    }

    /**
     * Remove user's role.
     * This method is used to remove user's role.
     *
     * @param string $role - Role value to be removed.
     *
     * @return self
     *
     * @throws ModelException - Model exception on invalid role.
     */
    public function removeRole(string $role): self
    {
        self::validateRole($role);

        $this->roles = array_filter($this->roles, function ($item) use ($role) {
            return $item !== $role;
        });
        $this->roles = array_values($this->roles);

        return $this;
    }

    /**
     * Check if user entity contain needed role.
     *
     * @param string $role - Role value to check if contains in user entity/
     *
     * @return bool
     *
     * @throws ModelException
     */
    public function hasRole(string $role): bool
    {
        self::validateRole($role);

        if (in_array($role, $this->getRoles())) {
            return true;
        }

        return false;
    }

    /**
     * Return relation with notification settings.
     * This method is used to return instance of user notification settings entity based on database relation.
     *
     * @return UserNotification
     */
    public function getNotification(): UserNotification
    {
        return $this->notification;
    }

    /**
     * Update relation with notification settings.
     * This method is used to update instance of user notification settings entity based on database relation.
     *
     * @param UserNotification $notification - User notification settings entity instance.
     *
     * @return User
     */
    public function setNotification(UserNotification $notification): User
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getPassword(): ?string
    {
        return '';
    }

    /**
     * Sensitive method for authentication.
     */
    public function getSalt()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function eraseCredentials()
    {
    }

    /**
     * Sensitive method for authentication.
     */
    public function getUsername()
    {
    }

    /**
     * {@inheritDoc}
     */
    #[Pure]
    public function getUserIdentifier(): string
    {
        return $this->getId();
    }

    /**
     * Validate role value.
     * This method is used to validate if role exist in the system and if value is right.
     *
     * @throws ModelException - Exception if argument $role do not exist in the system.
     */
    public static function validateRole(string $role, bool $exception = true): bool
    {
        if (in_array($role, self::ROLES)) {
            return true;
        }

        if ($exception) {
            throw new ModelException(sprintf(
                    'Role %s is not valid role. Allowed roles %s',
                    $role,
                    implode(', ', self::ROLES))
            );
        }

        return false;
    }
}
