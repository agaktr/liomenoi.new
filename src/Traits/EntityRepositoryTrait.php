<?php

namespace App\Traits;

use Doctrine\ORM\EntityRepository;
use Predis\Client;
use Symfony\Component\Cache\Adapter\RedisAdapter;

trait EntityRepositoryTrait
{

    private string $where = '';
    private array $whereParams = [];

    public function count(array $criteria)
    {

        $this->createWhereClause($criteria);

        /** @var EntityRepository $this */
        return $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where($this->where)
            ->setParameters($this->whereParams)
            ->getQuery()
            ->setResultCache(new RedisAdapter(new Client()))
            ->getSingleScalarResult();
    }

    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
    {

        $this->createWhereClause($criteria);

        /** @var EntityRepository $this */
        return $this->createQueryBuilder('e')
            ->where($this->where)
            ->setParameters($this->whereParams)
            ->orderBy('e.'.$orderBy[0], $orderBy[1])
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->setResultCache(new RedisAdapter(new Client()))
            ->getResult();
    }

    //function to create the where clause
    //based on criteria
    private function createWhereClause(array $criteria)
    {

        $where = '1=1';
        $params = [];

        foreach ($criteria as $key => $value) {

            if (empty($value)) {
                unset($criteria[$key]);
                continue;
            }

            switch (is_object($value)) {
                case false:
                    $where .= " AND e.{$key} LIKE :{$key}";
                    $params[$key] = "%$value%";
                    break;
                default:
                    $where .= " AND e.{$key} = :{$key}";
                    $params[$key] = $value->getId();
                    break;
            }
        }

        $this->where = $where;
        $this->whereParams = $params;
    }
}