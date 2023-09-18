<?php

declare(strict_types=1);

namespace MinVWS\AuditLoggerBundle\DependencyInjection;

use MinVWS\AuditLogger\AuditLogger;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use OldSound\RabbitMqBundle\OldSoundRabbitMqBundle;

class AuditLoggerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Since env vars are not automatically resolved in the config, we need to do it ourselves.
        // See: https://github.com/symfony/symfony/issues/40794
        array_walk_recursive($config, function (&$value) use ($container) {
            $value = $container->resolveEnvPlaceholders($value, true);
        });

        $this->loadServices($container, $config);
    }

    private function loadServices(ContainerBuilder $container, array $config): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');


        $definition = new Definition(AuditLogger::class);
        $container->setDefinition(AuditLogger::class, $definition);

        $encryption_configured = ! empty($config['encryption']['public_key']);
        $container->getDefinition('audit_logger.encryption')->replaceArgument(0, $config['encryption']['public_key']);
        $container->getDefinition('audit_logger.encryption')->replaceArgument(1, $config['encryption']['private_key']);

        if ($config['loggers']['psr_logger']['enabled']) {
            $container->getDefinition('audit_logger.psr_logger')->replaceArgument(1, $config['loggers']['psr_logger']['log_pii']);
            if ($config['loggers']['psr_logger']['encrypted'] && ! $encryption_configured) {
                throw new \Exception('PSR logger is configured to log encrypted data, but no encryption keys are configured.');
            }
            if (! $config['loggers']['psr_logger']['encrypted']) {
                $container->getDefinition('audit_logger.psr_logger')->replaceArgument(2, null);
            }
            $definition->addMethodCall('addLogger', [new Reference('audit_logger.psr_logger')]);
        } else {
            $container->removeDefinition('audit_logger.psr_logger');
        }

        if ($config['loggers']['file_logger']['enabled']) {
            $container->getDefinition('audit_logger.file_logger')->replaceArgument(0, $config['loggers']['file_logger']['path']);
            $container->getDefinition('audit_logger.file_logger')->replaceArgument(1, $config['loggers']['file_logger']['log_pii']);
            if ($config['loggers']['file_logger']['encrypted'] && ! $encryption_configured) {
                throw new \Exception('File logger is configured to log encrypted data, but no encryption keys are configured.');
            }
            if (! $config['loggers']['file_logger']['encrypted']) {
                $container->getDefinition('audit_logger.file_logger')->replaceArgument(2, null);
            }
            $definition->addMethodCall('addLogger', [new Reference('audit_logger.file_logger')]);
        } else {
            $container->removeDefinition('audit_logger.file_logger');
        }

<<<<<<< Updated upstream
        if ($config['loggers']['doctrine_logger']['enabled']) {
            $container->getDefinition('audit_logger.doctrine_logger')->replaceArgument(1, $config['loggers']['doctrine_logger']['log_pii']);
            if ($config['loggers']['doctrine_logger']['encrypted'] && ! $encryption_configured) {
                throw new \Exception('Doctrine logger is configured to log encrypted data, but no encryption keys are configured.');
            }
            if (! $config['loggers']['doctrine_logger']['encrypted']) {
                $container->getDefinition('audit_logger.doctrine_logger')->replaceArgument(2, null);
            }
            $definition->addMethodCall('addLogger', [new Reference('audit_logger.doctrine_logger')]);
        } else {
            $container->removeDefinition('audit_logger.doctrine_logger');
=======
        if (isset($config['loggers']['doctrine_logger'])) {
            $container->getDefinition(DoctrineLogger::class)->setArgument(
                2,
                $config['loggers']['doctrine_logger']['log_pii']
            );

            // Create and add encryption class
            $definition = new Definition(EncryptionHandler::class);
            $definition->setArgument(0, $config['loggers']['doctrine_logger']['encrypted']);
            $definition->setArgument(1, $config['encryption']['public_key']);
            $definition->setArgument(2, $config['encryption']['private_key']);
            $container->getDefinition(DoctrineLogger::class)->setArgument(0, $definition);

            $definition = $container->getDefinition(AuditLogger::class);
            $definition->addMethodCall('addLogger', [new Reference(DoctrineLogger::class)]);
>>>>>>> Stashed changes
        }

        if ($config['loggers']['rabbitmq_logger']['enabled']) {
            if (!class_exists(OldSoundRabbitMqBundle::class, false)) {
                throw new \Exception(
                    'RabbitMQ logger is configured to log data, but the RabbitMQ bundle is not installed. ' .
                    'Please try and run "composer require php-amqplib/rabbitmq-bundle"'
                );
            }

            $container->getDefinition('audit_logger.rabbitmq_logger')->replaceArgument(0, new Reference($config['loggers']['rabbitmq_logger']['producer_service']));
            $container->getDefinition('audit_logger.rabbitmq_logger')->replaceArgument(1, $config['loggers']['rabbitmq_logger']['routing_key']);
            $container->getDefinition('audit_logger.rabbitmq_logger')->replaceArgument(2, $config['loggers']['rabbitmq_logger']['additional_events']);

<<<<<<< Updated upstream
            $container->getDefinition('audit_logger.rabbitmq_logger')->replaceArgument(3, $config['loggers']['rabbitmq_logger']['log_pii']);
            if ($config['loggers']['rabbitmq_logger']['encrypted'] && ! $encryption_configured) {
                throw new \Exception('RabbitMQ logger is configured to log encrypted data, but no encryption keys are configured.');
            }
            if (! $config['loggers']['rabbitmq_logger']['encrypted']) {
                $container->getDefinition('audit_logger.rabbitmq_logger')->replaceArgument(4, null);
            }
            $definition->addMethodCall('addLogger', [new Reference('audit_logger.rabbitmq_logger')]);
        } else {
            $container->removeDefinition('audit_logger.rabbitmq_logger');
=======
            $container->getDefinition(RabbitmqLogger::class)->setArgument(
                1,
                new Reference($config['loggers']['rabbitmq_logger']['producer_service'])
            );
            $container->getDefinition(RabbitmqLogger::class)->setArgument(
                2,
                $config['loggers']['rabbitmq_logger']['routing_key']
            );
            $container->getDefinition(RabbitmqLogger::class)->setArgument(
                3,
                $config['loggers']['rabbitmq_logger']['additional_events']
            );

            $definition = $container->getDefinition(AuditLogger::class);
            $definition->addMethodCall('addLogger', [new Reference(RabbitmqLogger::class)]);
>>>>>>> Stashed changes
        }
    }
}
