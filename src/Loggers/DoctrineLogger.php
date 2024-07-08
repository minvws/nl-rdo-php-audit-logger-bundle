<?php

declare(strict_types=1);

namespace MinVWS\AuditLoggerBundle\Loggers;

use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use MinVWS\AuditLogger\Handlers\EncryptionHandler;
use MinVWS\AuditLogger\Events\Logging\GeneralLogEvent;
use MinVWS\AuditLogger\Loggers\LogEventInterface;
use MinVWS\AuditLogger\Loggers\LoggerInterface;
use MinVWS\AuditLoggerBundle\Entity\AuditEntry;
use MinVWS\AuditLoggerBundle\Entity\EncryptedAuditEntry;

class DoctrineLogger implements LoggerInterface
{
    protected bool $logPiiData;
    protected EncryptionHandler $encryptionHandler;
    protected EntityManagerInterface $doctrine;

    public function __construct(
        EncryptionHandler $encryptionHandler,
        EntityManagerInterface $doctrine,
        bool $logPiiData = false,
    ) {
        $this->encryptionHandler = $encryptionHandler;
        $this->doctrine = $doctrine;
        $this->logPiiData = $logPiiData;
    }

    public function log(LogEventInterface $event): void
    {
        $data = ($this->logPiiData) ? $event->getMergedPiiData() : $event->getLogData();

        if ($this->encryptionHandler->isEnabled()) {
            $dataAsJsonString = json_encode($data, JSON_THROW_ON_ERROR);
            $data = $this->encryptionHandler->encrypt($dataAsJsonString);
            $entity = new EncryptedAuditEntry();
            $entity->setCreatedAt(CarbonImmutable::now());
            $entity->setData($data);
        } else {
            assert($data['created_at'] instanceof \DateTimeImmutable);

            $entity = new AuditEntry();
            $entity->setCreatedAt(CarbonImmutable::now());
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
