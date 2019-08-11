<?php

namespace Deozza\PhilarmonyCoreBundle\Tests\testProjectMysql\src\Repository;

use Deozza\PhilarmonyCoreBundle\Tests\testProjectMysql\src\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findByUsernameOrEmail($username, $email)
    {
        $parameters = [
            'username' => $username,
            'email' => $email
        ];
        $queryBuilder = $this->createQueryBuilder('u');
        $queryBuilder
            ->select('u')
            ->where('u.username = :username')
            ->orWhere('u.email = :email')
            ->setParameters($parameters);

        return $queryBuilder->getQuery()->getResult();
    }
}
