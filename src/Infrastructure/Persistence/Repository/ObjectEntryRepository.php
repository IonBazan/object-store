<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Entity\ObjectEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ObjectEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ObjectEntry::class);
    }

    public function save(ObjectEntry $entry): void
    {
        $this->_em->persist($entry);
        $this->_em->flush();
    }

    public function findByKeyAtTime(string $key, \DateTime $time): ?ObjectEntry
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.key = :key')
            ->andWhere('e.createdAt <= :time')
            ->setParameters(['key' => $key, 'time' => $time])
            ->orderBy('e.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
