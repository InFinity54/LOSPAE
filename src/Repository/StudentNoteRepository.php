<?php

namespace App\Repository;

use App\Entity\StudentNote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StudentNote>
 *
 * @method StudentNote|null find($id, $lockMode = null, $lockVersion = null)
 * @method StudentNote|null findOneBy(array $criteria, array $orderBy = null)
 * @method StudentNote[]    findAll()
 * @method StudentNote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StudentNoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StudentNote::class);
    }

    //    /**
    //     * @return StudentNote[] Returns an array of StudentNote objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?StudentNote
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
