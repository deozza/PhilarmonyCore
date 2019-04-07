<?php

namespace Deozza\PhilarmonyBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 */
class EntityJoin
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="uuid", unique=true)
     * @JMS\Accessor(getter="getUuidAsString")
     */
    protected $uuid;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $kind;

    /**
     * @ORM\Column(type="object")
     */
    private $owner;

    /**
     * @ORM\ManyToOne(targetEntity="Deozza\PhilarmonyBundle\Entity\Entity", inversedBy="entityJoin")
     */
    private $entity1;

    private $entity1uuid;

    /**
     * @ORM\ManyToOne(targetEntity="Deozza\PhilarmonyBundle\Entity\Entity", inversedBy="entityJoin")
     */
    private $entity2;

    private $entity2uuid;

    /**
     * @ORM\OneToMany(targetEntity="Deozza\PhilarmonyBundle\Entity\Property", mappedBy="entityJoin")
     */
    private $properties;

    public function __construct()
    {
        $this->properties = new ArrayCollection();
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

    public function getKind(): ?string
    {
        return $this->kind;
    }

    public function setKind(string $kind): self
    {
        $this->kind = $kind;

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

    public function getEntity1(): ?Entity
    {
        return $this->entity1;
    }

    public function setEntity1(Entity $entity1): self
    {
        $this->entity1 = $entity1;
        return $this;
    }

    public function getEntity1uuid(): ?string
    {
        return $this->entity1uuid;
    }

    public function setEntity1uuid(string $entity1uuid): self
    {
        $this->entity1uuid = $entity1uuid;
        return $this;
    }

    public function getEntity2(): ?Entity
    {
        return $this->entity2;
    }


    public function setEntity2(Entity $entity2): self
    {
        $this->entity2 = $entity2;
        return $this;
    }

    public function getEntity2uuid(): ?string
    {
        return $this->entity2uuid;
    }

    public function setEntity2uuid(string $entity2uuid): self
    {
        $this->entity2uuid = $entity2uuid;
        return $this;
    }



    public function getProperties()
    {
        return $this->properties;
    }

}
