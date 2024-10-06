<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Compte les utilisateurs ayant un rôle particulier
     *
     * @param string $role Le rôle à rechercher
     *
     * @return int Le nombre d'utilisateurs ayant le rôle spécifié
     * @throws Exception
     */
    public function countByRole(string $role): int
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT COUNT(*) as count FROM user u
            WHERE JSON_CONTAINS(u.roles, :role)
        ';

        $stmt = $conn->prepare($sql);
        $stmt->bindValue('role', json_encode($role));
        $result = $stmt->executeQuery()->fetchAssociative();

        return (int) $result['count'];
    }

    /**
     * Récupère les utilisateurs ayant un rôle particulier avec pagination
     *
     * @param string $role Le rôle à rechercher
     * @param int $limit Nombre maximum de résultats
     * @param int $start L'offset de départ pour la pagination
     *
     * @return array Liste d'utilisateurs ayant le rôle spécifié
     */
    public function findByRole(string $role, int $limit, int $start): array
    {
        $conn = $this->getEntityManager()->getConnection();

        // Création du ResultSetMapping pour mapper vers l'entité User
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(User::class, 'u');

        $sql = '
            SELECT * FROM user u
            WHERE JSON_CONTAINS(u.roles, :role)
            ORDER BY u.last_name ASC, u.first_name ASC
            LIMIT :limit OFFSET :start
        ';

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter('role', json_encode($role));
        $query->setParameter('limit', $limit);
        $query->setParameter('start', $start);

        return $query->getResult();
    }


    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
