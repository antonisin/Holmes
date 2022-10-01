<?php

namespace App\Entity\Extend;

use Doctrine\ORM\Mapping as ORM;

/**
 * Info personal number trait.
 * This trait used to describe all fields and properties related to personal info number. Because the full system is
 * based on store and search for personal number, this properties may be reused and that's why exist this trait.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.1.0
 */
trait InfoNumberTrait
{
    /**
     * Personal number value.
     * This property contains first part of personal number (uniq value).
     *
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    private int $number;

    /**
     * Request year.
     * This property contain second part of personal number (year of request).
     *
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    private int $year;

    /**
     * Personal number code/type.
     * This property contain special code value. This code value sometimes exists in required numbers.
     *
     * @var string|null
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private string|null $code;


    /**
     * Return personal number value.
     * This method is used to return first part of personal number (uniq value).
     *
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * Setup personal number value.
     * This method is used to update first part of personal number (uniq value).
     *
     * @param int $number - First part of personal number (uniq id value).
     *
     * @noinspection PhpDocSignatureInspection
     *
     * @return self
     */
    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Return request year.
     * This method is used to return second part of personal number (year of request).
     *
     * @return int
     */
    public function getYear(): int
    {
        return $this->year;
    }

    /**
     * Setup request year.
     * This method is used to update second part of personal number (year of request).
     *
     * @param int $year - Second part of personal number (year of request).
     *
     * @noinspection PhpDocSignatureInspection
     *
     * @return self
     */
    public function setYear(int $year): self
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Return personal number code/type.
     * This method is used to return special code value. This code value sometimes exists in required numbers.
     *
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * Update personal number code/type.
     * This method is used to update special code value. This code value sometimes exists in required numbers.
     *
     * @param string $code - Special personal number code/type. Sometimes presents in required numbers.
     *
     * @noinspection PhpDocSignatureInspection
     *
     * @return self
     */
    public function setCode(string $code): self
    {
        $this->code = strtoupper($code);

        return $this;
    }

    /**
     * Return formatter string.
     * This method is used to return formatted string value, more familiar for user.
     *
     * @return string
     */
    public function getFormatted(): string
    {
        if (empty($this->code)) {
            return sprintf('%d/%d', $this->number, $this->year);
        }

        return sprintf('%d/%s/%d', $this->number, $this->code, $this->year);
    }
}