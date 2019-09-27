<?php

namespace Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\src\Repository;

use Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\src\Document\ApiToken;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

class ApiTokenRepository extends DocumentRepository
{
    public function __construct(DocumentManager $dm)
    {
        $uow = $dm->getUnitOfWork();
        $classMetaData = $dm->getClassMetadata(ApiToken::class);
        parent::__construct($dm, $uow, $classMetaData);
    }
}
