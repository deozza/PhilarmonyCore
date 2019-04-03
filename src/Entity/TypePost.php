<?php
namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class TypePost
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
    private $regex;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getRegex(): ?string
    {
        return $this->regex;
    }

    public function setRegex($regex): self
    {
        $this->regex = $regex;
        return $this;
    }

}