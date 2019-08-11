<?php

namespace Deozza\PhilarmonyCoreBundle\Repository\MongoDB;

use Deozza\PhilarmonyCoreBundle\Document\Entity;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @method Entity[]    findAll()
 * @method Entity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntityRepository extends DocumentRepository
{
    public function __construct(DocumentManager $dm)
    {
        $uow = $dm->getUnitOfWork();
        $classMetaData = $dm->getClassMetadata(Entity::class);
        parent::__construct($dm, $uow, $classMetaData);
    }

    public function findAllAuthorized(string $entityName, array $possibleStates, $user)
    {
        $queryBuilder = $this->createQueryBuilder()->find('Entity');
        $queryBuilder->field("kind")->equals($entityName);

        foreach($possibleStates as $state => $config)
        {
            $queryBuilder->field("validationState")->equals($state);

            if(!empty($user))
            {
                $parameters['uuid'] = $user->getUuidAsString();
                if($config === 'owner')
                {
                }
            }
        }

        return $queryBuilder->getQuery()->execute();
    }

    public function findAllFiltered(Array $filters, Array $sort, $kind)
    {
        $queryBuilder = $this->createQueryBuilder()
            ->find(Entity::class)
            ->eagerCursor(true)
            ->field('kind')->equals($kind);

        if(!empty($filters))
        {
            foreach($filters as $field=>$value)
            {
                $field = explode('.', $field);
                $filter = $field[1];
                for($i = 2; $i < count($field); $i++)
                {
                    $filter .= ".".$field[$i];
                }

                if(is_numeric($value)) $value = (int) $value;

                switch ($field[0])
                {
                    case "equal"          : $queryBuilder->field($filter)->equals($value);break;
                    case "less"           : $queryBuilder->field($filter)->lt($value);break;
                    case "greater"        : $queryBuilder->field($filter)->gt($value);break;
                    case "lessOrEqual"    : $queryBuilder->field($filter)->lte($value);break;
                    case "greaterOrEqual" : $queryBuilder->field($filter)->gte($value);break;
                    case "like"           : $queryBuilder->field($filter)->equals(new \MongoRegex('/.*'.$value.'.*/i'));break;
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
                    $queryBuilder->sort("properties.".$field[1], $order);
                }
                else
                {
                    $queryBuilder->sort($field[0], $order);
                }
            }
        }
        return $queryBuilder->getQuery()->execute()->toArray();
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
}
