<?php

namespace Deozza\PhilarmonyCoreBundle\Repository;

use Deozza\PhilarmonyCoreBundle\Document\Entity;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @method Entity[]    findAll()
 * @method Entity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntityRepository extends DocumentRepository
{

/*
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Entity::class);
    }

    public function findAllAuthorized(string $entityName, array $possibleStates, $user)
    {
        $queryBuilder = $this->createQueryBuilder('e');
        $parameters = [];

        $queryBuilder->select('e');
        $queryBuilder->where("e.kind = :kind");
        $parameters['kind'] = $entityName;


        foreach($possibleStates as $state => $config)
        {
            $queryBuilder->andWhere("e.validationState = '$state'");

            if(!empty($user))
            {
                $parameters['uuid'] = $user->getUuidAsString();
                if($config === 'owner')
                {
                }
            }
        }

        $queryBuilder->setParameters($parameters);
        return $queryBuilder->getQuery()->execute();
    }

    public function findAllFiltered(Array $filters, Array $sort, $kind)
    {
        $queryBuilder = $this->createQueryBuilder('e');
        $queryBuilder->select('e');
        $parameters = [];
        $queryBuilder->where('e.kind = :kind');
        $parameters["kind"] = $kind;

        if(!empty($filters))
        {
            foreach($filters as $field=>$value)
            {
                $field = explode('.', $field);
                $operator = "LIKE";
                switch ($field[0])
                {
                    case "equal": $operator = "=";break;
                    case "lesser": $operator = "<";break;
                    case "greater": $operator = ">";break;
                    case "lesserOrEqual": $operator = "<=";break;
                    case "greaterOrEqual": $operator = ">=";break;
                    case "like": $operator = "LIKE";break;
                }

                if(is_numeric($value) === false)
                {
                    $value = preg_replace("/(['])/", "''", $value);
                    if($operator === "LIKE")
                    {
                        $value = "%".$value."%";
                    }
                    $value = "'".$value."'";
                }

                if($field[1] === "properties")
                {
                    $filter = $field[2];
                    for($i = 3; $i < count($field); $i++)
                    {
                        $filter .= ".".$field[$i];
                    }
                    $queryBuilder->andWhere("JSON_EXTRACT(e.properties,'$.".$filter."') $operator $value");
                }
                else
                {
                    $queryBuilder->andWhere("e.".$field[1]." $operator $value");
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

    public function findAllForValidate($kind, $property, $value, $operator, ?array $referenceParams)
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

    public function findAllBetweenForValidate($kind, $propertyMin, $propertyMax, $value, ?array $referenceParams)
    {
        $parameters = [];

        $queryBuilder = $this->createQueryBuilder('e');
        $queryBuilder->select('e');
        $queryBuilder->andWhere('e.kind = :kind');

        try
        {
            $is_a_date = new \DateTime($value);
            $queryBuilder->andWhere("JSON_UNQUOTE(JSON_EXTRACT(e.properties, '$.".$propertyMin.".date'))  <= :value");
            $queryBuilder->andWhere("JSON_UNQUOTE(JSON_EXTRACT(e.properties, '$.".$propertyMax.".date'))  >= :value");
            $parameters["value"] = $value;
        }
        catch(\Exception $e)
        {
            $queryBuilder->andWhere("JSON_EXTRACT(e.properties, '$.".$propertyMin."')>= :value");
            $queryBuilder->andWhere("JSON_EXTRACT(e.properties, '$.".$propertyMax."')<= :value");
            $parameters["value"] = $value;
        }

        if(!empty($referenceParams))
        {
            foreach($referenceParams as $key=>$value)
            {
                $queryBuilder->andWhere("JSON_EXTRACT(e.properties, '$.".$key."') = '".$value."'");
            }
        }

        $parameters["kind"] = $kind;

        $queryBuilder->setParameters($parameters);
        return $queryBuilder->getQuery()->execute();
    }
*/
}
