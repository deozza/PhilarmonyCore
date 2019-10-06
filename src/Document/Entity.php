<?php

namespace Deozza\PhilarmonyCoreBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;
use Ramsey\Uuid\Uuid;

/**
 * @ODM\Document(repositoryClass="Deozza\PhilarmonyCoreBundle\Repository\EntityRepository")
 */
class Entity
{
    /**
     * @ODM\Id(strategy="NONE", type="string")
     * @JMS\Groups({"entity_id", "entity_complete"})
     */
    private $uuid;

    /**
     * @ODM\Field(type="string")
     * @JMS\Groups({"entity_complete", "entity_basic"})
     */
    private $kind;

    /**
     * @ODM\Field(type="string")
     * @JMS\Groups({"entity_complete", "entity_basic"})
     */
    private $validationState;

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
     * @ODM\ReferenceMany(
     *     targetDocument="Deozza\PhilarmonyCoreBundle\Document\Property",
     *     discriminatorField="kind",
     *     mappedBy="entity",
     *     storeAs="dbRef")
     * @JMS\Groups({"entity_complete", "entity_basic", "entity_property"})
     */
    private $properties;

    public function __construct()
    {
        $this->setUuid();
        $this->dateOfCreation = new \DateTime('now');
        $this->lastUpdate = $this->dateOfCreation;
        $this->properties = new ArrayCollection();
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

    public function getPropertiesByKind(string $kind)
    {
        return $this->getProperties()->filter(function (Property $property) use ($kind) {
            return $property->getKind() === $kind;
        });
    }

    public function addProperties(Property $property)
    {
        $this->properties[] = $property;
    }
}
