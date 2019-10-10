<?php

namespace Deozza\PhilarmonyCoreBundle\Repository;

use Deozza\PhilarmonyCoreBundle\Document\Entity;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

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

    public function findAllFiltered(Array $filters, Array $sort, string $kind)
    {
        $queryBuilder = $this->createQueryBuilder()
            ->find(Entity::class)
            ->eagerCursor(true)
            ->field('kind')->equals($kind);

        if(!empty($userUuid))
        {
            $queryBuilder->field('owner.uuid')->equals($userUuid);
        }

        if(!empty($validationStates))
        {
            foreach($validationStates as $state)
            {
                $queryBuilder->addOr($queryBuilder->expr()->field('validationState')->equals($state));
            }
        }

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
                try
                {
                    $this->returnOperator($field[0], $queryBuilder, $filter, $value);
                }
                catch(\Exception $e)
                {
                    return null;
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

        return $queryBuilder;
    }

    public function findFilteredAndPaginated(Array $filters, Array $sort, string $kind, array $validationStates, int $count, int $page, string $userUuid = null)
    {
        $queryBuilder = $this->findAllFiltered($filters, $sort, $kind);
        $pagination = [];
        $pagination['total'] = $queryBuilder->getQuery()->execute()->count();

        if(!empty($count) && !empty($page))
        {
            $queryBuilder->limit($count);
            $offset = ($page - 1) * $count;
            $queryBuilder->skip($offset);
            $pagination['current_page_number'] = $page;
            $pagination['num_items_per_page'] = $count;
        }

        $pagination['items'] =  $queryBuilder->getQuery()->execute()->toArray();

        return $pagination;
    }

    public function findAllForValidate($kind, $property, $value, $operator, ?array $referenceParams)
    {
        $queryBuilder = $this->createQueryBuilder()
            ->find(Entity::class)
            ->eagerCursor(true)
            ->field('kind')->equals($kind);
        $this->returnOperator($operator, $queryBuilder, $property, $value);

        return $queryBuilder->getQuery()->execute()->toArray();
    }

    public function findAllBetweenForValidate($kind, $propertyMin, $propertyMax, $value, ?array $referenceParams)
    {
        $queryBuilder = $this->createQueryBuilder()
            ->find(Entity::class)
            ->eagerCursor(true)
            ->field('kind')->equals($kind);

        $queryBuilder->field($propertyMin)->lte($value);
        $queryBuilder->field($propertyMax)->gte($value);
        if(!empty($referenceParams))
        {
            foreach($referenceParams as $key=>$value)
            {
                $queryBuilder->field($key)->equals($value);
            }
        }

        return $queryBuilder->getQuery()->execute()->toArray();
    }

    private function returnOperator(string $operator, $queryBuilder, string $field, $value)
    {
        switch($operator)
        {
            case "equal"          : $queryBuilder->field($field)->equals($value);break;
            case "less"           : $queryBuilder->field($field)->lt($value);break;
            case "greater"        : $queryBuilder->field($field)->gt($value);break;
            case "lessOrEqual"    : $queryBuilder->field($field)->lte($value);break;
            case "greaterOrEqual" : $queryBuilder->field($field)->gte($value);break;
            case "like"           : $queryBuilder->field($field)->equals(new \MongoRegex('/.*'.$value.'.*/i'));break;
            default: throw new \Exception();break;
        }
    }
}
