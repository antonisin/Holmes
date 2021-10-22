<?php

namespace App\Entity;

use App\Entity\Extend\InfoNumberTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[
    ORM\Entity(),
    ORM\HasLifecycleCallbacks,
]
class UserInfoNumber extends BaseEntity
{
    use InfoNumberTrait;


    #[
        ORM\ManyToOne(targetEntity: User::class),
        ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE'),
    ]
    private User|UserInterface $user;


    /**
     * @return User|UserInterface
     */
    public function getUser(): UserInterface|User
    {
        return $this->user;
    }

    /**
     * @param User|UserInterface $user
     *
     * @return UserInfoNumber
     */
    public function setUser(UserInterface|User $user): UserInfoNumber
    {
        $this->user = $user;

        return $this;
    }
}