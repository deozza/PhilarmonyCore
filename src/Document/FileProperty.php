<?php

namespace Deozza\PhilarmonyCoreBundle\Document;


use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\HttpFoundation\File\File;
use Ramsey\Uuid\Uuid;
use JMS\Serializer\Annotation as JMS;

/**
 * @ODM\Document(repositoryClass="Deozza\PhilarmonyCoreBundle\Repository\FilePropertyRepository")
 */
class FileProperty
{
    /**
     * @ODM\Id(strategy="NONE", type="string")
     * @JMS\Groups({"entity_id", "entity_complete", "file_id"})
     */
    private $uuid;

    /**
     * @JMS\Exclude()
     */
    private $file;

    /**
     * @ODM\Field(type="raw")
     * @JMS\Groups({"entity_complete", "entity_basic"})
     */
    private $owner;

    /**
     * @ODM\Field(type="string")
     * @JMS\Groups({"entity_complete", "entity_basic"})
     */
    private $property;

    /**
     * @ODM\Field(type="string")
     * @JMS\Groups({"entity_complete", "entity_basic"})
     */
    private $filename;

    /**
     * @ODM\Field(type="string")
     * @JMS\Groups({"entity_complete", "entity_basic"})
     */
    private $filetitle;

    /**
     * @ODM\Field(type="string")
     * @JMS\Groups({"entity_complete", "entity_basic"})
     */
    private $description;

    /**
     * @ODM\Field(type="string")
     * @JMS\Groups({"entity_complete", "entity_basic"})
     */
    private $credit;

    /**
     * @ODM\Field(type="string")
     * @JMS\Groups({"entity_complete", "entity_basic"})
     */
    private $mimetype;

    /**
     * @ODM\Field(type="date")
     * @JMS\Groups({"entity_complete", "entity_basic"})
     */
    private $dateOfUpload;

    public function __construct($owner, Property $property)
    {
        $this->setUuid();
        $this->owner = $owner;
        $this->property = $property->getUuidAsString();
    }

    public function setUuid(): self
    {
        $this->uuid = Uuid::uuid4()->toString();
        return $this;
    }

    public function getUuidAsString(): ?string
    {
        return $this->uuid;
    }

    public function setFile(string $file): void
    {
        $this->file = $file;
        $this->dateOfUpload = new \DateTime('now');
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setMimetype(string $mimetype): void
    {
        $this->mimetype = $mimetype;
    }

    public function getMimetype(): ?string
    {
        return $this->mimetype;
    }

    public function setFilename(?string $filename): void
    {
        $this->filename = $filename;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFiletitle(?string $filetitle): void
    {
        $this->filetitle = $filetitle;
    }

    public function getFiletitle(): ?string
    {
        return $this->filetitle;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setCredit(?string $credit): void
    {
        $this->credit = $credit;
    }

    public function getCredit(): ?string
    {
        return $this->credit;
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    public function getProperty()
    {
        return $this->property;
    }

    public function setProperty(string $property)
    {
        $this->property = $property;

        return $this;
    }
}