<?php

namespace App\Entity;

use App\Entity\Extend\InfoNumberTrait;
use Doctrine\ORM\Mapping as ORM;

#[
    ORM\Entity,
    ORM\HasLifecycleCallbacks
]
class InfoNumber extends BaseEntity
{
    use InfoNumberTrait;


    #[
        ORM\ManyToOne(targetEntity: Source::class),
        ORM\JoinColumn(name: 'source_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE'),
    ]
    private Source $source;


    /**
     * @return Source
     */
    public function getSource(): Source
    {
        return $this->source;
    }

    /**
     * @param Source $source
     *
     * @return InfoNumber
     */
    public function setSource(Source $source): InfoNumber
    {
        $this->source = $source;

        return $this;
    }
}