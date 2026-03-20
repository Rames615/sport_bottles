<?php

namespace App\Repository;

use App\Entity\Promotion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Promotion>
 */
class PromotionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Promotion::class);
    }

    /**
     * @return Promotion[]
     */

    public function findActivePromotions(): array
    {
        $now = new \DateTimeImmutable();

        return $this->createQueryBuilder('p')
            ->addSelect('product')
            ->leftJoin('p.product', 'product')
            ->where('p.isActive = true')
            ->andWhere('p.startAt <= :now')
            ->andWhere('p.endAt >= :now')
            ->setParameter('now', $now)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
