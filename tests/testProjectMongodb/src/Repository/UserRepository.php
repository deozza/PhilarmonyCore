<?php

namespace Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\src\Repository;

use Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\src\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

class UserRepository extends DocumentRepository
{
    public function __construct(DocumentManager $dm)
    {
        $uow = $dm->getUnitOfWork();
        $classMetaData = $dm->getClassMetadata(User::class);
        parent::__construct($dm, $uow, $classMetaData);
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
