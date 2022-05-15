<?php

namespace App\Entity;

use App\Entity\Extend\InfoNumberTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Info Number doctrine entity model.
 * This class is implemented as doctrine entity model to work and manipulate with info numbers. Info numbers - are
 * records parsed from pdf files and stored in out database. As base properties and fields, info numbers are the same
 * as user numbers (contain years and numbers).
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.0.0
 */
#[
    ORM\Entity,
    ORM\HasLifecycleCallbacks
]
class InfoNumber extends BaseEntity
{
    use InfoNumberTrait;


    /**
     * Source entity instance.
     * This property contain related (via database relation) wit source entity instance. It shows source records and
     * document where info number was parsed.
     *
     * @var Source
     */
    #[
        ORM\ManyToOne(targetEntity: Source::class),
        ORM\JoinColumn(name: 'source_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE'),
    ]
    private Source $source;


    /**
     * Return source entity instance.
     * This method is designed to return related (via database relation) source entity instance where was info number
     * parsed.
     *
     * @return Source
     */
    public function getSource(): Source
    {
        return $this->source;
    }

    /**
     * Setup source entity instance.
     * This method is designed to update related (via database relation) source entity instance where was info number
     * parsed.
     *
     * @param Source $source - Source doctrine entity instance.
     *
     * @return InfoNumber
     */
    public function setSource(Source $source): InfoNumber
    {
        $this->source = $source;

        return $this;
    }
}
