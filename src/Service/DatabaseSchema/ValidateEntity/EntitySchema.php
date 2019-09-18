<?php

namespace Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema\ValidateEntity;

class EntitySchema
{
    private $entityName;
    private $properties;
    private $states;
    private $constraints;

    public function getEntityName(): ?string
    {
        return $this->entityName;
    }

    public function setEntityName(string $entityName): self
    {
        $this->entityName = $entityName;
        return $this;
    }

    public function getProperties(): ?array
    {
        return $this->properties;
    }

    public function setProperties(array $properties): self
    {
        $this->properties = $properties;
        return $this;
    }

    public function getStates(): ?array
    {
        return $this->states;
    }

    public function setStates(array $states): self
    {
        $this->states = $states;
        return $this;
    }

    public function getConstraints(): ?array
    {
        return $this->constraints;
    }

    public function setConstraints(array $constraints): self
    {
        $this->constraints = $constraints;
        return $this;
    }
}