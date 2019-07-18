<?php

namespace Deozza\PhilarmonyCoreBundle\Tests\testProject\src\Repository;

use Deozza\PhilarmonyCoreBundle\Tests\testProject\src\Entity\RealUser;
use Deozza\PhilarmonyUserBundle\Repository\UserRepository as BaseUserRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method RealUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method RealUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method RealUser[]    findAll()
 * @method RealUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RealUserRepository extends BaseUserRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, RealUser::class);
    }

}
