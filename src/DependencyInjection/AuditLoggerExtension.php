<?php

declare(strict_types=1);

namespace MinVWS\AuditLoggerBundle\DependencyInjection;

use MinVWS\AuditLogger\AuditLogger;
use MinVWS\AuditLogger\EncryptionHandler;
use MinVWS\AuditLogger\Loggers\FileLogger;
use MinVWS\AuditLogger\Loggers\PsrLogger;
use MinVWS\AuditLoggerBundle\Loggers\DoctrineLogger;
use MinVWS\AuditLoggerBundle\Loggers\RabbitmqLogger;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use OldSound\RabbitMqBundle\OldSoundRabbitMqBundle;

/**
 * @phpstan-type ConfigurationArray array{
 *   encryption: array{public_key: scalar, private_key: scalar},
 *   loggers: array{
 *     psr_logger?: array{enabled: bool, encrypted: bool, log_pii: bool},
 *     doctrine_logger?: array{enabled: bool, encrypted: bool, log_pii: bool},
 *     file_logger?: array{enabled: bool, encrypted: bool, log_pii: bool, path: scalar},
 *     rabbitmq_logger?: array{
 *       enabled: bool,
 *       encrypted: bool,
 *       log_pii: bool,
 *       producer_service: scalar,
 *       routing_key: scalar,
 *       additional_events: array<string, string>
 *     },
 *   }
 * }
 */
class AuditLoggerExtension extends Extension
{
    /**
     * @param array<mixed> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        /**
         * @phpstan-var ConfigurationArray $config
         * @var array<mixed> $config
         */
        $config = $this->processConfiguration($configuration, $configs);

        $this->loadServices($container, $config);
    }

    /**
     * @param ConfigurationArray $config
     */
    private function loadServices(ContainerBuilder $container, array $config): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        if (isset($config['loggers']['psr_logger'])) {
            $container->getDefinition(PsrLogger::class)->setArgument(2, $config['loggers']['psr_logger']['log_pii']);

            // Create and add encryption class
            $definition = new Definition(EncryptionHandler::class);
            $definition->setArgument(0, $config['loggers']['psr_logger']['encrypted']);
            $definition->setArgument(1, $config['encryption']['public_key']);
            $definition->setArgument(2, $config['encryption']['private_key']);
            $container->getDefinition(PsrLogger::class)->setArgument(0, $definition);

            $definition = $container->getDefinition(AuditLogger::class);
            $definition->addMethodCall('addLogger', [new Reference(PsrLogger::class)]);
        }

        if (isset($config['loggers']['file_logger'])) {
            $container->getDefinition(FileLogger::class)->setArgument(1, $config['loggers']['file_logger']['path']);
            $container->getDefinition(FileLogger::class)->setArgument(2, $config['loggers']['file_logger']['log_pii']);

            // Create and add encryption class
            $definition = new Definition(EncryptionHandler::class);
            $definition->setArgument(0, $config['loggers']['file_logger']['encrypted']);
            $definition->setArgument(1, $config['encryption']['public_key']);
            $definition->setArgument(2, $config['encryption']['private_key']);
            $container->getDefinition(FileLogger::class)->setArgument(0, $definition);

            $definition = $container->getDefinition(AuditLogger::class);
            $definition->addMethodCall('addLogger', [new Reference(FileLogger::class)]);
        }

        if (isset($config['loggers']['doctrine_logger'])) {
            $container
                ->getDefinition(DoctrineLogger::class)
                ->setArgument(2, $config['loggers']['doctrine_logger']['log_pii']);

            // Create and add encryption class
            $definition = new Definition(EncryptionHandler::class);
            $definition->setArgument(0, $config['loggers']['doctrine_logger']['encrypted']);
            $definition->setArgument(1, $config['encryption']['public_key']);
            $definition->setArgument(2, $config['encryption']['private_key']);
            $container->getDefinition(DoctrineLogger::class)->setArgument(0, $definition);

            $definition = $container->getDefinition(AuditLogger::class);
            $definition->addMethodCall('addLogger', [new Reference(DoctrineLogger::class)]);
        }

        if (isset($config['loggers']['rabbitmq_logger'])) {
            if (!class_exists(OldSoundRabbitMqBundle::class, false)) {
                throw new \Exception(
                    'RabbitMQ logger is configured to log data, but the RabbitMQ bundle is not installed. " .
                    "Please try and run "composer require php-amqplib/rabbitmq-bundle"',
                );
            }

            // Create and add encryption class
            $definition = new Definition(EncryptionHandler::class);
            $definition->setArgument(0, $config['loggers']['rabbitmq_logger']['encrypted']);
            $definition->setArgument(1, $config['encryption']['public_key']);
            $definition->setArgument(2, $config['encryption']['private_key']);
            $container->getDefinition(RabbitmqLogger::class)->setArgument(0, $definition);

            $container
                ->getDefinition(RabbitmqLogger::class)
                ->setArgument(1, new Reference((string) $config['loggers']['rabbitmq_logger']['producer_service']));
            $container
                ->getDefinition(RabbitmqLogger::class)
                ->setArgument(2, $config['loggers']['rabbitmq_logger']['routing_key']);
            $container
                ->getDefinition(RabbitmqLogger::class)
                ->setArgument(3, $config['loggers']['rabbitmq_logger']['additional_events']);

            $definition = $container->getDefinition(AuditLogger::class);
            $definition->addMethodCall('addLogger', [new Reference(RabbitmqLogger::class)]);
        }
    }
}
