<?php

namespace Deozza\PhilarmonyCoreBundle\Tests\testProject\src\Repository;

use Deozza\PhilarmonyCoreBundle\Tests\testProject\src\Document\ApiToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ApiToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApiToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApiToken[]    findAll()
 * @method ApiToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApiTokenRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ApiToken::class);
    }
}
