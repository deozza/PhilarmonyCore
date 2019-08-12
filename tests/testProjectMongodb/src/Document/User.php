<?php
namespace Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\src\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\User\UserInterface;
use JMS\Serializer\Annotation as JMS;
use Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\src\Repository\UserRepository;

/**
 * @ODM\Document(repositoryClass=UserRepository::class)
 */

class User implements UserInterface
{
    /**
     * @ODM\Id(strategy="NONE", type="string")
     * @JMS\Groups({"user_id", "entity_complete"})
     */
    private $uuid;

    /**
     * @ODM\Field(type="string")
     * @JMS\Groups({"user_basic", "username", "entity_complete"})
     */
    private $username;

    /**
     * @ODM\Field(type="string")
     *@JMS\Groups({"user_basic"})
     */
    private $email;

    private $plainPassword;

    private $newPassword;

    /**
     * @ODM\Field(type="string")
     *@JMS\Exclude()
     */
    private $password;

    /**
     * @ODM\Field(type="date")
     * @JMS\Groups({"user_advanced"})
     */
    private $lastLogin;

    /**
     * @ODM\Field(type="date")
     * @JMS\Groups({"user_advanced"})
     */
    private $lastFailedLogin;

    /**
     * @ODM\Field(type="date")
     * @JMS\Groups({"user_advanced"})
     */
    private $registerDate;

    /**
     * @ODM\Field(type="boolean")
     * @JMS\Groups({"user_advanced"})
     */
    private $active;

    /**
     * @ODM\Field(type="collection")
     * @JMS\Groups({"user_advanced"})
     */
    private $roles = [];

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->active = false;
        $this->registerDate = new \DateTime('now');
    }

    public function setUuid(): self
    {
        $this->uuid = Uuid::uuid4()->toString();
        return $this;
    }

    public function getUuidAsString(): ?string
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