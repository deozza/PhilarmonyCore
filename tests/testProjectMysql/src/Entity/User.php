<?php
namespace Deozza\PhilarmonyCoreBundle\Tests\testProjectMysql\src\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Deozza\PhilarmonyCoreBundle\Tests\testProjectMysql\src\Repository\UserRepository;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */

class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @JMS\Groups({"user_id", "entity_complete"})
     */
    private $id;
    /**
     * @ORM\Column(type="uuid", unique=true)
     * @JMS\Accessor(getter="getUuidAsString")
     * @JMS\Groups({"user_id", "entity_complete"})
     */
    protected $uuid;
    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @JMS\Groups({"user_basic", "username", "entity_complete"})
     */
    protected $username;

    /**
     * @Assert\Type("string")
     * @JMS\Exclude
     */
    protected $plainPassword;

    /**
     * @Assert\Type("string")
     * @JMS\Exclude
     */
    protected $newPassword;

    /**
     * @ORM\Column(type="string", length=255)
     * @JMS\Exclude
     */
    protected $password;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email."
     * )
     * @JMS\Groups({"user_basic"})
     */
    protected $email;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @JMS\Groups({"user_advanced"})
     */
    protected $lastLogin;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @JMS\Groups({"user_advanced"})
     */
    protected $lastFailedLogin;

    /**
     * @ORM\Column(type="datetime")
     * @JMS\Groups({"user_advanced"})
     */
    protected $registerDate;

    /**
     * @ORM\Column(type="boolean")
     * @JMS\Groups({"user_advanced"})
     */
    protected $active;


    /**
     * @ORM\Column(type="json")
     * @JMS\Groups({"user_advanced"})
     */
    protected $roles = [];


    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->active = false;
        $this->registerDate = new \DateTime('now');
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

    public function getUuid()
    {
        return $this->uuid;
    }

    public function getUuidAsString(): ?string
    {
        if(empty($this->uuid)) return null;
        return $this->uuid->toString();
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