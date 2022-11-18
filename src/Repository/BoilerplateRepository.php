<?php

namespace App\Repository;

use App\Entity\Boilerplate;
use App\Traits\EntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Predis\Client;
use Symfony\Component\Cache\Adapter\RedisAdapter;

/**
 * @extends ServiceEntityRepository<Boilerplate>
 *
 * @method Boilerplate|null find($id, $lockMode = null, $lockVersion = null)
 * @method Boilerplate|null findOneBy(array $criteria, array $orderBy = null)
 * @method Boilerplate[]    findAll()
 * @method Boilerplate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BoilerplateRepository extends ServiceEntityRepository
{

    use EntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Boilerplate::class);
    }

    public function add(Boilerplate $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Boilerplate $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


//    /**
//     * @return Boilerplate[] Returns an array of Boilerplate objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Boilerplate
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
