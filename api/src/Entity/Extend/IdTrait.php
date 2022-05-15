<?php

namespace App\Entity\Extend;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity id trait.
 * This trait contains properties and methods to describe id field/property for standard model/entity.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.0.0
 */
trait IdTrait
{
    /**
     * Entity id value.
     * This property contain model/entity identifier value.
     *
     * @var int
     */
    #[
        ORM\Id,
        ORM\Column(type: "integer"),
        ORM\GeneratedValue(strategy: "AUTO")
    ]
    protected int $id;


    /**
     * Return entity id.
     * This method will return model identifier value.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}
