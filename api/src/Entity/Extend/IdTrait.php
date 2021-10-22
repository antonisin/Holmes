<?php

namespace App\Entity\Extend;

use Doctrine\ORM\Mapping as ORM;

trait IdTrait
{
    #[
        ORM\Id,
        ORM\Column(type: "integer"),
        ORM\GeneratedValue(strategy: "AUTO")
    ]
    protected int $id;
}