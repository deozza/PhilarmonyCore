<?php

namespace Deozza\PhilarmonyBundle\Repository;

use Deozza\PhilarmonyBundle\Entity\Entity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @method Entity[]    findAll()
 * @method Entity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntityRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Entity::class);
    }


    public function findAllFiltered(Array $filters, $kind)
    {
        $queryBuilder = $this->createQueryBuilder('e');
        $queryBuilder->select('e');
        $parameters = [];
        $queryBuilder->andWhere('e.kind = :kind');
        $parameters["kind"] = $kind;


        if(!empty($filters))
        {
            foreach ($filters as $filter=>$value)
            {
                $queryBuilder->andWhere("JSON_CONTAINS(e.properties, :$filter, '$.".$filter."') = 1");
                $parameters[$filter] = json_encode($value);
            }

        }

        $queryBuilder->setParameters($parameters);
        return $queryBuilder->getQuery();

    }

}
