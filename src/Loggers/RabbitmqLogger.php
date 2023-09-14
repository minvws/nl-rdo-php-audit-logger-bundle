<?php

declare(strict_types=1);

namespace MinVWS\AuditLoggerBundle\Loggers;

use MinVWS\AuditLogger\EncryptionHandler;
use MinVWS\AuditLogger\Events\Logging\GeneralLogEvent;
use MinVWS\AuditLogger\Loggers\LogEventInterface;
use MinVWS\AuditLogger\Loggers\LoggerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

class RabbitmqLogger implements LoggerInterface
{
    protected bool $logPiiData;
    protected ?EncryptionHandler $encryptionHandler;
    protected ProducerInterface $auditLogProducer;
    protected string $routingKey;
    /** @var string[]  */
    protected array $additional_events;

    public function __construct(
        ProducerInterface $auditLogProducer,
        string $routingKey = '',
        $additional_events = [],
        bool $logPiiData = false,
        EncryptionHandler $encryptionHandler = null
    ) {
        $this->encryptionHandler = $encryptionHandler;
        $this->logPiiData = $logPiiData;
        $this->auditLogProducer = $auditLogProducer;
        $this->routingKey = $routingKey;
        $this->additional_events = $additional_events;
    }

    public function log(LogEventInterface $event): void
    {
        $data = ($this->logPiiData) ? $event->getMergedPiiData() : $event->getLogData();

        if ($this->encryptionHandler !== null) {
            $data = $this->encryptionHandler->encrypt($data);
        } else {
            $data = json_encode($data);
        }

        $this->auditLogProducer->publish($data, $this->routingKey);
    }

    public function canHandleEvent(LogEventInterface $event): bool
    {
        if (is_a($event, GeneralLogEvent::class)) {
            return true;
        }

        return false;
    }
}
