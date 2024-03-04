<?php

namespace App\Repository;

use App\Entity\NoteChange;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NoteChange>
 *
 * @method NoteChange|null find($id, $lockMode = null, $lockVersion = null)
 * @method NoteChange|null findOneBy(array $criteria, array $orderBy = null)
 * @method NoteChange[]    findAll()
 * @method NoteChange[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NoteChangeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NoteChange::class);
    }

    //    /**
    //     * @return NoteChange[] Returns an array of NoteChange objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('n.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?NoteChange
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
