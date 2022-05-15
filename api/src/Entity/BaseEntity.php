<?php

namespace App\Entity;

use App\Entity\Extend\CreatedAtTrait;
use App\Entity\Extend\IdTrait;
use App\Entity\Extend\UpdatedAtTrait;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;

/**
 * Base doctrine entity model.
 * This class is implemented to be extended and used as base doctrine entity model with all default and required
 * properties (like id, createdAt, updatedAt) and methods.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.0.0
 */
class BaseEntity
{
    use IdTrait;
    use CreatedAtTrait;
    use UpdatedAtTrait;


    /**
     * PrePersist method.
     * This method is called every time when NEW model/entity was stored into database. (Life cycle doctrine events).
     */
    #[ORM\PrePersist]
    public function prePersist()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * PrePersist method.
     * This method is called every time when model/entity was updated in database. (Life cycle doctrine events).
     *
     * @param PreUpdateEventArgs $opt - Contain options and values of new and old values (before and after class change)
     */
    #[ORM\PreUpdate]
    public function preUpdate(PreUpdateEventArgs $opt)
    {
        $this->updatedAt = new \DateTime();
    }
}