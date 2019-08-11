<?php

namespace Deozza\PhilarmonyCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="Deozza\PhilarmonyCoreBundle\Repository\MySQL\EntityRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Entity
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @JMS\Exclude()
     */
    private $id;

    /**
     * @ORM\Column(type="uuid", unique=true)
     * @JMS\Accessor(getter="getUuidAsString")
     * @JMS\Groups({"entity_id", "entity_complete"})
     */
    protected $uuid;

    /**
     * @ORM\Column(type="string", length=255)
     * @JMS\Groups({"entity_complete", "entity_basic"})
     */
    private $kind;


    /**
     * @ORM\Column(type="string", length=255)
     * @JMS\Groups({"entity_complete", "entity_basic"})
     */
    private $validationState;


    /**
     * @ORM\Column(type="array")
     * @JMS\Groups({"entity_complete", "entity_basic"})
     */
    private $owner;

    /**
     * @ORM\Column(type="datetime")
     * @JMS\Groups({"entity_complete", "entity_basic"})
     */
    private $dateOfCreation;

    /**
     * @ORM\Column(type="datetime")
     * @JMS\Groups({"entity_complete", "entity_basic"})
     */
    private $lastUpdate;

    /**
     * @ORM\Column(type="json")
     * @JMS\Groups({"entity_complete", "entity_basic", "entity_property"})
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

    /**
     * @ORM\PrePersist
     */
    public function setupUuid()
    {
        $this->setUuid(Uuid::uuid4());
        return $this;
    }

    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function getUuidAsString()
    {
        return $this->uuid->toString();
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
