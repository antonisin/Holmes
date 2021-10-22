<?php

namespace App\Entity\Extend;

use App\Entity\InfoNumber;
use Doctrine\ORM\Mapping as ORM;

trait InfoNumberTrait
{
    #[ORM\Column(type: 'integer')]
    private int $number;

    #[ORM\Column(type: 'integer')]
    private int $year;


    /**
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @param int $number
     *
     * @return self
     */
    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @return int
     */
    public function getYear(): int
    {
        return $this->year;
    }

    /**
     * @param int $year
     *
     * @return self
     */
    public function setYear(int $year): self
    {
        $this->year = $year;

        return $this;
    }
}