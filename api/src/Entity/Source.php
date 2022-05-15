<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Source doctrine entity model.
 * This class is implemented as doctrine entity to work and manage source information. Source records contain base
 * information about file and used on parse pdf process. On each downloaded pdf, system will create new source record.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.0.0
 */
#[
    ORM\Entity(),
    ORM\HasLifecycleCallbacks
]
class Source extends BaseEntity
{
    /**
     * File name value.
     * This property contain file name from web interface on origin service. This value do not contain file extension
     * or path parts. Example: 562P.
     *
     * @var string
     */
    #[ORM\Column(type: 'string')]
    private string $fileName;

    /**
     * File real name.
     * This property contain real file name with file extension. Real name is filename downloaded from origin service.
     * Value do not contain any path parts. Example: ORDIN-562P-ART-11.pdf.
     *
     * @var string
     */
    #[ORM\Column(type: 'string')]
    private string $fileNameReal;

    /**
     * File url value.
     * This property contain original url to file from origin service. This url was used to download file and also can
     * be used to access original file.
     * Example: http://cetatenie.just.ro/wp-content/uploads/2022/01/ORDIN-562P-ART-11.pdf.
     *
     * @var string
     */
    #[ORM\Column(type: 'string')]
    private string $fileUrl;

    /**
     * Processed at date and time.
     * This property contain date and time when source file was parsed.
     *
     * @var \DateTime|null
     */
    #[ORM\Column(type: 'datetime', nullable: true, options: ["default" => "CURRENT_TIMESTAMP"])]
    protected ?\DateTime $processedAt = null;


    /**
     * Return file name value.
     * This method is used to return file name used in web interface on origin service. This value do not contain file
     * extension or path parts. Example: 562P.
     *
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * Update file name value.
     * This method is used to update file name value used in web interface on origin service. This value do not contain
     * file extension or path parts. Example: 562P.
     *
     * @param string $fileName - File name used on origin web service interface.
     */
    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Return file url value.
     * This method is used to return original url to file from origin service. This url was used to download file and
     * also can be used to access original file.
     * Example: http://cetatenie.just.ro/wp-content/uploads/2022/01/ORDIN-562P-ART-11.pdf.
     *
     * @return string
     */
    public function getFileUrl(): string
    {
        return $this->fileUrl;
    }

    /**
     * Update file url value.
     * This method is used to update original url to file from origin service. This url was used to download file and
     * also can be used to access original file.
     * Example: http://cetatenie.just.ro/wp-content/uploads/2022/01/ORDIN-562P-ART-11.pdf.
     *
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
     * Return file real name.
     * This method is used to return real file name with file extension. Real name is filename downloaded from origin
     * service. Value do not contain any path parts. Example: ORDIN-562P-ART-11.pdf.
     *
     * @return string
     */
    public function getFileNameReal(): string
    {
        return $this->fileNameReal;
    }

    /**
     * Update file real name.
     * This method is used to update real file name with file extension. Real name is filename downloaded from origin
     * service. Value do not contain any path parts. Example: ORDIN-562P-ART-11.pdf.
     *
     * @param string $fileNameReal - Real file name value (with extension. ex: pdf).
     *
     * @return Source
     */
    public function setFileNameReal(string $fileNameReal): Source
    {
        $this->fileNameReal = $fileNameReal;

        return $this;
    }

    /**
     * Return processed at date and time.
     * This method is used to return date and time when source file was parsed.
     *
     * @return \DateTime|null
     */
    public function getProcessedAt(): ?\DateTime
    {
        return $this->processedAt;
    }

    /**
     * Update processed at date and time.
     * This method is used to update date and time when source file was parsed.
     *
     * @param \DateTime|null $processedAt - Date and time when source file was processed/parsed.
     *
     * @return Source
     */
    public function setProcessedAt(?\DateTime $processedAt): Source
    {
        $this->processedAt = $processedAt;

        return $this;
    }
}