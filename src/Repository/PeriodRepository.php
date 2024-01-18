<?php

namespace App\Repository;

use App\Entity\Period;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Period>
 *
 * @method Period|null find($id, $lockMode = null, $lockVersion = null)
 * @method Period|null findOneBy(array $criteria, array $orderBy = null)
 * @method Period[]    findAll()
 * @method Period[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PeriodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Period::class);
    }

    // Fonction pour vérifier si les dates chevauchent les périodes existantes en BDD
    public function findOverlappingPerriods(\DateTimeInterface $startDate, 
    \DateTimeInterface $endDate) 
    {
        // Initialise un objet QueryBuilder lié à l'entité des périodes
        $qb = $this->createQueryBuilder('p')
        // Vérifie si la date de début de la période en base 
        // est inférieure ou égale à la date de fin spécifiée
        ->where('p.startDate <= :endDate')
        // Vérifie si la date de fin de la période en base 
        // est supérieure ou égale à la date de début spécifiée
        ->andWhere('p.endDate >= :startDate')
        // Définit les valeurs des paramètres
        ->setParameters([
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);

        // Exécute la requête et renvoie les résultats
        return $qb->getQuery()->getResult();
    }

    }

//    /**
//     * @return Period[] Returns an array of Period objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Period
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

