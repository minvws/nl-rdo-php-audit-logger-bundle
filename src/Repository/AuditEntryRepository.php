<?php

namespace MinVWS\AuditLoggerBundle\Repository;

use MinVWS\AuditLoggerBundle\Entity\AuditEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AuditEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method AuditEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method AuditEntry[] findAll()
 * @method AuditEntry[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuditEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuditEntry::class);
    }
}
