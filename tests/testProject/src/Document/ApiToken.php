<?php
namespace Deozza\PhilarmonyCoreBundle\Tests\testProject\src\Document;

use Deozza\PhilarmonyCoreBundle\Tests\testProject\src\Document\User;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ODM\Document(repositoryClass="Deozza\PhilarmonyCoreBundle\Repository\EntityRepository")
 */
class ApiToken
{
    /**
     * @ODM\Id()
     * @JMS\Exclude
     */
    private $id;

    /**
     * @ODM\Field(type="string")
     */
    private $token;

    /**
     * @ODM\ReferenceOne(targetDocument="User")
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