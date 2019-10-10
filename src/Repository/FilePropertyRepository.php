<?php

namespace Deozza\PhilarmonyCoreBundle\Repository;

use Deozza\PhilarmonyCoreBundle\Document\FileProperty;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

/**
 * @method FileProperty|null find($id, $lockMode = null, $lockVersion = null)
 * @method FileProperty|null findOneBy(array $criteria, array $orderBy = null)
 * @method FileProperty[]    findAll()
 * @method FileProperty[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FilePropertyRepository extends DocumentRepository
{

}
