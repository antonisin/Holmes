<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.0.0
 */
#[
    ORM\Entity(),
    ORM\HasLifecycleCallbacks
]
class User extends BaseEntity implements UserInterface
{
    public const DEFAULT_TYPE = 'DEFAULT_TYPE';
    public const GOOGLE_TYPE  = 'GOOGLE_TYPE';

    #[ORM\Column(type: 'string')]
    private string $type = self::DEFAULT_TYPE;

    #[ORM\Column(type: 'string')]
    private string $email;

    #[ORM\Column(type: 'string')]
    private string $firstName;

    #[ORM\Column(type: 'string')]
    private string $lastName;

    #[ORM\Column(type: 'string', nullable: true)]
    private string|null $picture = null;

    #[ORM\Column(type: 'string')]
    private string $refId;


    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     *
     * @return User
     */
    public function setFirstName(string $firstName): User
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     *
     * @return User
     */
    public function setLastName(string $lastName): User
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPicture(): ?string
    {
        return $this->picture;
    }

    /**
     * @param string|null $picture
     *
     * @return User
     */
    public function setPicture(?string $picture): User
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * @return string
     */
    public function getRefId(): string
    {
        return $this->refId;
    }

    /**
     * @param string $refId
     */
    public function setRefId(string $refId): self
    {
        $this->refId = $refId;

        return $this;
    }

    public function getRoles()
    {
        return [];
        // TODO: Implement getRoles() method.
    }

    public function getPassword()
    {
        // TODO: Implement getPassword() method.
    }

    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUsername()
    {
        // TODO: Implement getUsername() method.
    }

    public function getUserIdentifier()
    {
        return $this->getEmail();
    }
}
