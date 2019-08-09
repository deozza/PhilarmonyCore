<?php
namespace Deozza\PhilarmonyCoreBundle\Tests\testProject\src\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ODM\Document
 */

class User implements UserInterface
{
    /**
     * @ODM\Id(strategy="UUID", type="string")
     */
    private $uuid;

    /** @ODM\Field(type="string") */
    private $username;

    /** @ODM\Field(type="string") */
    private $email;

    private $plainPassword;

    private $newPassword;

    /** @ODM\Field(type="string") */
    private $password;

    /** @ODM\Field(type="date") */
    private $lastLogin;

    /** @ODM\Field(type="date") */
    private $lastFailedLogin;

    /** @ODM\Field(type="date") */
    private $registerDate;

    /** @ODM\Field(type="boolean") */
    private $active;

    /** @ODM\Field(type="collection") */
    private $roles = [];

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->active = false;
        $this->registerDate = new \DateTime('now');
    }


    public function getUuid()
    {
        return $this->uuid;
    }


    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setNewPassword(string $newPassword): self
    {
        $this->newPassword = $newPassword;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTimeInterface $lastLogin): self
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    public function getLastFailedLogin(): ?\DateTimeInterface
    {
        return $this->lastFailedLogin;
    }

    public function setLastFailedLogin(?\DateTimeInterface $lastFailedLogin): self
    {
        $this->lastFailedLogin = $lastFailedLogin;

        return $this;
    }

    public function getRegisterDate(): ?\DateTimeInterface
    {
        return $this->registerDate;
    }

    public function setRegisterDate(\DateTimeInterface $registerDate): self
    {
        $this->registerDate = $registerDate;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getRoles() :array
    {
        return array_unique(array_merge(['ROLE_USER'], $this->roles));
    }

    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }


    public function getSalt()
    {
    }
    public function eraseCredentials()
    {
    }

}