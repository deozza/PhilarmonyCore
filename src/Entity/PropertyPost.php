<?php
namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class PropertyPost
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
    private $type;

    /**
     * @Assert\Type("boolean")
     */

    private $is_required;
    /**
     * @Assert\Type("boolean")
     */
    private $unique;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getisRequired(): ?bool
    {
        return $this->is_required;
    }

    public function setIsRequired(bool $is_required): self
    {
        $this->is_required = $is_required;
        return $this;
    }

    public function getUnique(): ?bool
    {
        return $this->unique;
    }
    public function setUnique(bool $unique): self
    {
        $this->unique = $unique;
        return $this;
    }
}