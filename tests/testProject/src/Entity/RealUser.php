<?php
namespace Deozza\PhilarmonyCoreBundle\Tests\testProject\src\Entity;

use Doctrine\ORM\Mapping as ORM;
use Deozza\PhilarmonyUserBundle\Entity\User as BaseUser;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="Deozza\PhilarmonyCoreBundle\Tests\testProject\src\Repository\RealUserRepository")
 */
class RealUser extends BaseUser
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @JMS\Groups({"user_id", "entity_complete"})
     */
    private $id;


    public function getId(): ?int
    {
        return $this->id;
    }
}