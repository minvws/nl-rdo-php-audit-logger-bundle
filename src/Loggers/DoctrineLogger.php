<?php

declare(strict_types=1);

namespace MinVWS\AuditLoggerBundle\Loggers;

use Doctrine\ORM\EntityManagerInterface;
use MinVWS\AuditLogger\EncryptionHandler;
use MinVWS\AuditLogger\Events\Logging\GeneralLogEvent;
use MinVWS\AuditLogger\Loggers\LogEventInterface;
use MinVWS\AuditLogger\Loggers\LoggerInterface;
use MinVWS\AuditLoggerBundle\Entity\AuditEntry;
use MinVWS\AuditLoggerBundle\Entity\EncryptedAuditEntry;

class DoctrineLogger implements LoggerInterface
{
    protected bool $logPiiData;
    protected ?EncryptionHandler $encryptionHandler;
    protected EntityManagerInterface $doctrine;

    public function __construct(EntityManagerInterface $doctrine, bool $logPiiData = false, EncryptionHandler $encryptionHandler = null)
    {
        $this->doctrine = $doctrine;
        $this->encryptionHandler = $encryptionHandler;
        $this->logPiiData = $logPiiData;
    }

    public function log(LogEventInterface $event): void
    {
        $data = ($this->logPiiData) ? $event->getMergedPiiData() : $event->getLogData();

        if ($this->encryptionHandler !== null) {
            $data = $this->encryptionHandler->encrypt($data);
            $entity = new EncryptedAuditEntry();
            $entity->setCreatedAt(new \DateTimeImmutable());
            $entity->setData($data);
        } else {
            $entity = new AuditEntry();
            $entity->setCreatedAt(new \DateTimeImmutable());
            $entity->setRequest($data);
            $entity->setCreatedAt($data['created_at']);
            $entity->setEventCode($data['event_code']);
            $entity->setActionCode($data['action_code']);
            $entity->setFailed($data['failed']);
        }

        $this->doctrine->persist($entity);
        $this->doctrine->flush();
    }

    public function canHandleEvent(LogEventInterface $event): bool
    {
        if (is_a($event, GeneralLogEvent::class)) {
            return true;
        }

        return false;
    }
}
