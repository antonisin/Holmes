<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[
    ORM\Entity(),
    ORM\HasLifecycleCallbacks
]
class Source extends BaseEntity
{
    #[ORM\Column(type: 'string')]
    private string $fileName;

    #[ORM\Column(type: 'string')]
    private string $fileNameReal;

    #[ORM\Column(type: 'string')]
    private string $fileUrl;


    #[ORM\Column(type: 'datetime', nullable: true, options: ["default" => "CURRENT_TIMESTAMP"])]
    protected ?\DateTime $processedAt = null;


    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     */
    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFileUrl(): string
    {
        return $this->fileUrl;
    }

    /**
     * @param string $fileUrl
     *
     * @return Source
     */
    public function setFileUrl(string $fileUrl): Source
    {
        $this->fileUrl = $fileUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getFileNameReal(): string
    {
        return $this->fileNameReal;
    }

    /**
     * @param string $fileNameReal
     *
     * @return Source
     */
    public function setFileNameReal(string $fileNameReal): Source
    {
        $this->fileNameReal = $fileNameReal;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getProcessedAt(): ?\DateTime
    {
        return $this->processedAt;
    }

    /**
     * @param \DateTime|null $processedAt
     *
     * @return Source
     */
    public function setProcessedAt(?\DateTime $processedAt): Source
    {
        $this->processedAt = $processedAt;

        return $this;
    }
}