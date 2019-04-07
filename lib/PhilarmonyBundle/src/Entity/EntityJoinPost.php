<?php
namespace Deozza\PhilarmonyBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class EntityJoinPost
{
    /**
     * @var string
     *
     * @Assert\Type("string")
     */
    private $name;

    /**
     * @var string
     *
     * @Assert\Type("string")
     */
    private $kind;

    private $entity1;

    private $entity2;

    private $properties;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
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

    public function getEntity1()
    {
        return $this->entity1;
    }

    public function setEntity1($entity1): self
    {
        $this->entity1 = $entity1;
        return $this;
    }

    public function getEntity2()
    {
        return $this->entity2;
    }

    public function setEntity2($entity2): self
    {
        $this->entity2 = $entity2;
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