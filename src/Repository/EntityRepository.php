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


    public function findAllFiltered(Array $propertyFilters,Array $entityFilters, $kind)
    {
        $queryBuilder = $this->createQueryBuilder('e');
        $queryBuilder->select('e');
        $parameters = [];
        $queryBuilder->andWhere('e.kind = :kind');
        $parameters["kind"] = $kind;


        if(!empty($propertyFilters))
        {
            foreach ($propertyFilters as $filter=>$value)
            {
                $queryBuilder->andWhere("JSON_EXTRACT(e.properties,'$.".$filter."') LIKE :$filter");
                $parameters[$filter] = "%$value%";
            }

        }

        if(!empty($entityFilters))
        {
            foreach ($entityFilters as $filter=>$value)
            {
                $queryBuilder->andWhere("e.$filter LIKE :$filter");
                $parameters[$filter] = "%$value%";
            }

        }

        $queryBuilder->setParameters($parameters);
        return $queryBuilder->getQuery()->execute();

    }

    public function findAllForValidate($kind, $property, $value, $operator)
    {
        $parameters = [];

        $queryBuilder = $this->createQueryBuilder('e');
        $queryBuilder->select('e');
        $queryBuilder->andWhere('e.kind = :kind');
        $queryBuilder->andWhere("JSON_EXTRACT(e.properties, '$.".$property."') $operator :value");

        $parameters["kind"] = $kind;
        $parameters["value"] = $value;

        $queryBuilder->setParameters($parameters);
        return $queryBuilder->getQuery()->execute();
    }

    public function findAllBetweenForValidate($kind, $propertyMin, $propertyMax, $value)
    {
        $parameters = [];

        $queryBuilder = $this->createQueryBuilder('e');
        $queryBuilder->select('e');
        $queryBuilder->andWhere('e.kind = :kind');
        $queryBuilder->andWhere("JSON_EXTRACT(e.properties, '$.".$propertyMin."')>= :value");
        $queryBuilder->andWhere("JSON_EXTRACT(e.properties, '$.".$propertyMax."')<= :value");

        $parameters["kind"] = $kind;
        $parameters["value"] = $value;

        $queryBuilder->setParameters($parameters);
        return $queryBuilder->getQuery()->execute();
    }

}
