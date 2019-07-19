<?php
namespace Deozza\PhilarmonyCoreBundle\Tests\testProject\src\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class Credentials
{
    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Type("string")
     */
    private $login;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Type("string")
     */
    private $password;

    /**
     * @return string
     */
    public function getLogin(): ?string
    {
        return $this->login;
    }

    /**
     * @param string $login
     */
    public function setLogin(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }



}