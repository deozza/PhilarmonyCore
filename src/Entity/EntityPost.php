<?php
namespace Deozza\PhilarmonyBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class EntityPost
{
    /**
     * @var string
     *
     * @Assert\Type("string")
     */
    private $name;

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