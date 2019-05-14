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


    public function findAllFiltered(Array $filters, Array $sort, $kind)
    {
        $queryBuilder = $this->createQueryBuilder('e');
        $queryBuilder->select('e');
        $parameters = [];
        $queryBuilder->andWhere('e.kind = :kind');
        $parameters["kind"] = $kind;


        if(!empty($filters))
        {
            foreach($filters as $field=>$value)
            {
                $field = explode('.', $field);
                $operator = "LIKE";

                if(is_numeric($value) === true)
                {
                    $operator = "=";
                }

                if($field[0] === "properties")
                {
                    $queryBuilder->andWhere("JSON_EXTRACT(e.properties,'$.".$field[1]."') $operator $value");
                }
                else
                {
                    $queryBuilder->andWhere("e.".$field[0]." $operator $value");
                }
            }
        }

        if(!empty($sort))
        {
            foreach($sort as $field=>$order)
            {
                $field = explode('.', $field);
                if($field[0] === "properties" )
                {
                    $queryBuilder->addOrderBy("JSON_EXTRACT(e.properties,'$.".$field[1]."') ", "$order");
                }
                else
                {
                    $queryBuilder->addOrderBy("e.".$field[0], "$order");
                }
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

        if(is_a($value, \DateTime::class))
        {
            $value = $value->format("Y-m-d")." 00:00:00.000000";
            $queryBuilder->andWhere("JSON_UNQUOTE(JSON_EXTRACT(e.properties, '$.".$propertyMin.".date'))  <= :value");
            $queryBuilder->andWhere("JSON_UNQUOTE(JSON_EXTRACT(e.properties, '$.".$propertyMax.".date'))  >= :value");
            $parameters["value"] = $value;

        }
        else
        {
            $queryBuilder->andWhere("JSON_EXTRACT(e.properties, '$.".$propertyMin."')>= :value");
            $queryBuilder->andWhere("JSON_EXTRACT(e.properties, '$.".$propertyMax."')<= :value");
            $parameters["value"] = $value;

        }


        $parameters["kind"] = $kind;

        $queryBuilder->setParameters($parameters);
        return $queryBuilder->getQuery()->execute();
    }

}
