<?php

namespace App\Entity\Extend;

use Doctrine\ORM\Mapping as ORM;

/**
 * Created At Trait.
 * This trait is used to describe created at date and time property and database field.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.0.0
 */
trait CreatedAtTrait
{
    /**
     * Created At datetime.
     * This property contain date and time when model/records was created.
     *
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', nullable: false, options: ["default" => "CURRENT_TIMESTAMP"])]
    protected \DateTime $createdAt;


    /**
     * Return created at date and time.
     * This method will return date and time when model/records was created/stored into database.
     *
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * Setup created at date and time.
     * This method will update date and time when model/records was created/stored into database.
     *
     * @param \DateTime $createdAt - DateTime instance when record was created.
     *
     * @noinspection PhpDocSignatureInspection
     *
     * @return self
     */
    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
