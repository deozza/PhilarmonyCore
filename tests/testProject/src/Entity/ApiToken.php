<?php
namespace Deozza\PhilarmonyCoreBundle\Tests\testProject\src\Entity;

use Deozza\PhilarmonyCoreBundle\Tests\testProject\src\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="Deozza\PhilarmonyCoreBundle\Tests\testProject\src\Repository\ApiTokenRepository")
 */
class ApiToken
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @JMS\Exclude
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $token;

    /**
     * @ORM\ManyToOne(targetEntity="Deozza\PhilarmonyCoreBundle\Tests\testProject\src\Entity\User")
     * @JMS\Exclude
     */
    private $user;

    public function __construct(User $user, $token)
    {
        $this->user = $user;
        $this->token = $token;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}