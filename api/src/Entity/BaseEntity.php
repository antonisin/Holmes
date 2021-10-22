<?php

namespace App\Entity;

use App\Entity\Extend\CreatedAtTrait;
use App\Entity\Extend\IdTrait;
use App\Entity\Extend\UpdatedAtTrait;
use Doctrine\ORM\Mapping as ORM;


class BaseEntity
{
    use IdTrait;
    use CreatedAtTrait;
    use UpdatedAtTrait;


    #[ORM\PrePersist]
    public function prePersist()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();
    }
}