<?php

namespace Deozza\PhilarmonyCoreBundle\Repository;

use Deozza\PhilarmonyCoreBundle\Document\Property;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

/**
 * @method Property|null find($id, $lockMode = null, $lockVersion = null)
 * @method Property|null findOneBy(array $criteria, array $orderBy = null)
 * @method Property[]    findAll()
 * @method Property[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PropertyRepository extends DocumentRepository
{

}
