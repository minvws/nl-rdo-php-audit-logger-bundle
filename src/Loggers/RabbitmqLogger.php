<?php

declare(strict_types=1);

namespace MinVWS\AuditLoggerBundle\Loggers;

use MinVWS\AuditLogger\Handlers\EncryptionHandler;
use MinVWS\AuditLogger\Events\Logging\GeneralLogEvent;
use MinVWS\AuditLogger\Loggers\LogEventInterface;
use MinVWS\AuditLogger\Loggers\LoggerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

class RabbitmqLogger implements LoggerInterface
{
    /**
     * @param EncryptionHandler $encryptionHandler
     * @param ProducerInterface $auditLogProducer
     * @param string $routingKey
     * @param array<array-key,string> $additionalEvents
     * @param boolean $logPiiData
     */
    public function __construct(
        protected EncryptionHandler $encryptionHandler,
        protected ProducerInterface $auditLogProducer,
        protected string $routingKey = '',
        protected array $additionalEvents = [],
        protected bool $logPiiData = false,
    ) {
    }

    public function log(LogEventInterface $event): void
    {
        $data = ($this->logPiiData) ? $event->getMergedPiiData() : $event->getLogData();
        $dataAsJsonString = json_encode($data, JSON_THROW_ON_ERROR);

        if ($this->encryptionHandler->isEnabled()) {
            $dataAsJsonString = $this->encryptionHandler->encrypt($dataAsJsonString);
        }

        $this->auditLogProducer->publish($dataAsJsonString, $this->routingKey);
    }

    public function canHandleEvent(LogEventInterface $event): bool
    {
        if (is_a($event, GeneralLogEvent::class)) {
            return true;
        }

        return false;
    }
}
