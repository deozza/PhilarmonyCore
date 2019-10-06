<?php

namespace Deozza\PhilarmonyCoreBundle\Document;


use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Ramsey\Uuid\Uuid;
use JMS\Serializer\Annotation as JMS;

/**
 * @ODM\Document()
 * @Vich\Uploadable
 */
class FileProperty
{
    /**
     * @ODM\Id(strategy="NONE", type="string")
     * @JMS\Groups({"file_id", "file_complete"})
     */
    private $uuid;

    /**
     * @Vich\UploadableField(mapping="property_file")
     * @var File
     */
    private $file;

    /**
     * @ODM\ReferenceOne(
     *     targetDocument="Deozza\PhilarmonyCoreBundle\Document\Property",
     *     inversedBy="files")
     * @JMS\Exclude())
     */
    private $property;

    /**
     * @ODM\Field(type="raw")
     * @JMS\Groups({"entity_complete", "entity_basic"})
     */
    private $owner;

    /**
     * @ODM\Field(type="date")
     * @JMS\Groups({"entity_complete", "entity_basic"})
     */
    private $dateOfUpload;

    public function __construct(Property $property, $owner)
    {
        $this->setUuid();
        $this->property = $property;
        $this->owner = $owner;
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

    public function setFile(?File $file = null): void
    {
        $this->file = $file;

        if (null !== $file) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->dateOfUpload = new \DateTime('now');
        }
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function getProperty(): Property
    {
        return $this->property;
    }

    public function setProperty(Entity $property): self
    {
        $this->property = $property;
        return $this;
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
}