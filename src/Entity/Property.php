<?php

namespace Deozza\PhilarmonyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 */
class Property
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
     * @JMS\Groups({"property_id", "property_complete"})
     */
    protected $uuid;

    /**
     * @ORM\Column(type="text")
     * @JMS\Groups({"property_basic", "property_complete"})
     */
    private $value;

    /**
     * @ORM\Column(type="string", length=255)
     * @JMS\Groups({"property_basic", "property_complete"})
     */
    private $kind;

    /**
     * @ORM\ManyToOne(targetEntity="Deozza\PhilarmonyBundle\Entity\Entity")
     * @JMS\Groups({"property_basic", "property_complete"})
     */
    private $entity;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
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

    public function getUuid(): ?UuidInterface
    {
        return $this->uuid;
    }

    public function getUuidAsString()
    {
        return $this->uuid->toString();
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;
        return $this;
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

    public function getEntity(): ?Entity
    {
        return $this->entity;
    }

    public function setEntity(Entity $entity): self
    {
        $this->entity = $entity;
        return $this;
    }


}
