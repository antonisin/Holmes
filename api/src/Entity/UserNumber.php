<?php

namespace App\Entity;

use App\Entity\Extend\InfoNumberTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User Number doctrine entity.
 * This class is implemented as doctrine entity model to work and manage user numbers records. User number - are record
 * stored by each user, used to find them in out database, or late when it will appear in our system. As base
 * properties and fields are the same as info number (contain year and number).
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.0.0
 */
#[
    ORM\Entity,
    ORM\HasLifecycleCallbacks,
]
class UserNumber extends BaseEntity
{
    use InfoNumberTrait;


    /**
     * Related user entity model.
     * This property contain related (based on database relation) user entity model (own).
     *
     * @var User|UserInterface
     */
    #[
        ORM\ManyToOne(targetEntity: User::class),
        ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE'),
    ]
    private User|UserInterface $user;

    /**
     * Related info number entity relation.
     * This property contain related info number entity instance in case when it was found in already parsed records.
     * In case when user's number is not yet found in system, this property will be empty.
     *
     * @var InfoNumber|null
     */
    #[
        ORM\ManyToOne(targetEntity: InfoNumber::class),
        ORM\JoinColumn(name: 'info_number_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE'),
    ]
    private null|InfoNumber $infoNumber;

    /**
     * Enable watching (continues search) of user number in records.
     * This property contain a boolean flag used to define if this user's number need to be used to continue search
     * in our database (if appeared).
     *
     * @var bool
     */
    #[ORM\Column(type: 'boolean', nullable: false, options: ["default" => true])]
    private bool $enabled = true;

    /**
     * Search At date and time.
     * This property contain date and time when last time user number was searched (used to search) in our database
     * records.
     *
     * @var \DateTime|null
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    protected null|\DateTime $searchAt;


    /**
     * Return related user entity model.
     * This method is used to return related (based on database relation) user entity model (own).
     *
     * @return User|UserInterface
     */
    public function getUser(): UserInterface|User
    {
        return $this->user;
    }

    /**
     * Update related user entity model.
     * This method is used to update related (based on database relation) user entity model (own).
     *
     * @param User|UserInterface $user - Related(owned) doctrine entity instance.
     *
     * @return UserNumber
     */
    public function setUser(UserInterface|User $user): UserNumber
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Check if watching (continues search) of user number in enabled or not.
     * This method is used to check if user number is enabled for continues search(watching).
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Update watching flag (continues search) of user number in enabled or not.
     * This method is designed to update watching flag state used on continues search(watching).
     *
     * @param bool $enabled - Enable or disable user number watching.
     *
     * @return UserNumber
     */
    public function setEnabled(bool $enabled): UserNumber
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Return search At date and time.
     * This method is used to return date and time when last time user number was searched (used to search) in our
     * database records.
     *
     * @return \DateTime|null
     */
    public function getSearchAt(): ?\DateTime
    {
        return $this->searchAt;
    }

    /**
     * Update search At date and time.
     * This method is used to update date and time when last time user number was searched (used to search) in our
     * database records.
     *
     * @param \DateTime $searchAt - DateTime instance when last time user number was used on search/watch process.
     *
     * @return UserNumber
     */
    public function setSearchAt(\DateTime $searchAt): UserNumber
    {
        $this->searchAt = $searchAt;

        return $this;
    }

    /**
     * Return related info number entity relation.
     * This method is used to return related info number entity instance in case when it was found in already parsed
     * records. In case when user's number is not yet found in system, this method will return null value.
     *
     * @return InfoNumber|null
     */
    public function getInfoNumber(): ?InfoNumber
    {
        return $this->infoNumber;
    }

    /**
     * Update related info number entity relation.
     * This method is used to update related info number entity instance in case when it was found in already parsed
     * records.
     *
     * @param InfoNumber $infoNumber - Related (found) info number doctrine entity instance.
     *
     * @return UserNumber
     */
    public function setInfoNumber(InfoNumber $infoNumber): UserNumber
    {
        $this->infoNumber = $infoNumber;

        return $this;
    }
}