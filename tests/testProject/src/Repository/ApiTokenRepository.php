<?php

namespace Deozza\PhilarmonyCoreBundle\Tests\testProject\src\Repository;

use Deozza\PhilarmonyCoreBundle\Tests\testProject\src\Document\ApiToken;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;

class ApiTokenRepository extends DocumentRepository
{
    public function __construct(DocumentManager $dm)
    {
        $uow = $dm->getUnitOfWork();
        $classMetaData = $dm->getClassMetadata(ApiToken::class);
        parent::__construct($dm, $uow, $classMetaData);
    }
}
