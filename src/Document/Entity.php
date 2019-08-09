<?php

namespace Deozza\PhilarmonyCoreBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ODM\Document(repositoryClass="Deozza\PhilarmonyCoreBundle\Repository\EntityRepository")
 */
class Entity
{
    /**
     * @ODM\Id(strategy="UUID", type="string")
     */
    private $uuid;

    /**
     * @ODM\Field(type="string")
     */
    private $kind;

    /**
     * @ODM\Field(type="string")
     */
    private $validationState;

    /**
     * @ODM\Field(type="hash")
     */
    private $owner;

    /**
     * @ODM\Field(type="date")
     */
    private $dateOfCreation;

    /**
     * @ODM\Field(type="date")
     */
    private $lastUpdate;

    /**
     * @ODM\Field(type="hash")
     */
    private $properties;

    public function __construct()
    {
        $this->dateOfCreation = new \DateTime('now');
        $this->lastUpdate = $this->dateOfCreation;
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getUuid()
    {
        return $this->uuid;
    }


    public function getKind(): ?string
    {
        return $this->kind;
    }

    public function setKind(string $kind): self
    {
        $this->kind = $kind;

        return $this;
    }

    public function getValidationState(): ?string
    {
        return $this->validationState;
    }

    public function setValidationState(string $validationState): self
    {
        $this->validationState = $validationState;

        return $this;
    }

    public function getDateOfCreation(): ?\DateTime
    {
        return $this->dateOfCreation;
    }

    public function getLastUpdate(): ?\DateTime
    {
        return $this->lastUpdate;
    }

    public function setLastUpdate(\DateTime $lastUpdate): ?self
    {
        $this->lastUpdate = $lastUpdate;
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

    public function getProperties()
    {
        return $this->properties;
    }

    public function setProperties($properties): self
    {
        $this->properties = $properties;
        return $this;
    }

}
