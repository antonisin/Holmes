<?php

namespace App\Entity\Extend;

use Doctrine\ORM\Mapping as ORM;

/**
 * Updated At Trait.
 * This trait is used to describe updated at date and time property and database field.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.0.0
 */
trait UpdatedAtTrait
{
    /**
     * Updated At datetime.
     * This property contain date and time when model/records was updated last time.
     *
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', nullable: false, options: ["default" => "CURRENT_TIMESTAMP"])]
    protected \DateTime $updatedAt;


    /**
     * Return updated at date and time.
     * This method will return date and time when model/records was updated.
     *
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * Setup updated at date and time.
     * This method will set up date and time when model/records was updated.
     *
     * @param \DateTime $updatedAt - DateTime instance when model was updated.
     *
     * @noinspection PhpDocSignatureInspection
     *
     * @return self
     */
    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
