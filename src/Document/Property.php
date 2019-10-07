<?php

namespace Deozza\PhilarmonyCoreBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;
use Ramsey\Uuid\Uuid;

/**
 * @ODM\EmbeddedDocument()
 */
class Property
{
    /**
     * @ODM\Id(strategy="NONE", type="string")
     * @JMS\Exclude()
     */
    private $uuid;

    /**
     * @ODM\Field(type="string")
     * @JMS\Groups({"entity_complete", "entity_basic"})
     */
    private $propertyName;

    /**
     * @ODM\Field(type="raw")
     * @JMS\Groups({"entity_complete", "entity_basic"})
     */
    private $owner;

    /**
     * @ODM\Field(type="date")
     * @JMS\Groups({"entity_complete", "entity_basic"})
     */
    private $dateOfCreation;

    /**
     * @ODM\Field(type="date")
     * @JMS\Groups({"entity_complete", "entity_basic"})
     */
    private $lastUpdate;

    /**
     * @ODM\Field(type="raw")
     * @JMS\Groups({"entity_complete", "entity_basic", "entity_property"})
     */
    private $data;

    /**
     * @ODM\EmbedMany(
     *     targetDocument="Deozza\PhilarmonyCoreBundle\Document\FileProperty",
     *     discriminatorField="kind",
     *     strategy="setArray")
     * @JMS\Groups({"entity_complete", "entity_basic", "entity_property"})
     */
    private $files;

    public function __construct(string $propertyName, Entity $entity)
    {
        $this->setUuid();
        $this->dateOfCreation = new \DateTime('now');
        $this->lastUpdate = $this->dateOfCreation;
        $this->propertyName = $propertyName;
        $this->files = new ArrayCollection();
    }

    public function getUuidAsString(): string
    {
        return $this->uuid;
    }

    public function setUuid(): self
    {
        $this->uuid = Uuid::uuid4()->toString();
        return $this;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }
    public function setPropertyName($propertyName): self
    {
        $this->propertyName = $propertyName;
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

    public function getDateOfCreation(): string
    {
        return $this->dateOfCreation;
    }

    public function setDateOfCreation(\DateTime $dateOfCreation): self
    {
        $this->dateOfCreation = $dateOfCreation;
        return $this;
    }

    public function getLastUpdate(): string
    {
        return $this->lastUpdate;
    }

    public function setLastUpdate(\DateTime $lastUpdate): self
    {
        $this->lastUpdate = $lastUpdate;
        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data): self
    {
        $this->data = $data;
        return $this;
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function addFiles(FileProperty $fileProperty)
    {
        $this->files[] = $fileProperty;
    }


}