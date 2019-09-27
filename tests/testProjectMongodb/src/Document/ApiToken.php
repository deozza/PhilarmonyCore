<?php
namespace Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\src\Document;

use Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\src\Repository\ApiTokenRepository;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ODM\Document(repositoryClass=ApiTokenRepository::class)
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
     * @ODM\ReferenceOne(targetDocument="Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\src\Document\User")
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