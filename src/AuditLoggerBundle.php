<?php

declare(strict_types=1);

namespace MinVWS\AuditLoggerBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use MinVWS\AuditLogger\AuditLogger;
use MinVWS\AuditLogger\Handlers\EncryptionHandler;
use MinVWS\AuditLogger\Loggers\FileLogger;
use MinVWS\AuditLogger\Loggers\PsrLogger;
use MinVWS\AuditLoggerBundle\Loggers\DoctrineLogger;
use MinVWS\AuditLoggerBundle\Loggers\RabbitmqLogger;
use OldSound\RabbitMqBundle\OldSoundRabbitMqBundle;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class AuditLoggerBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
            ->arrayNode('encryption')
                ->children()
                    ->scalarNode('public_key')->end()
                    ->scalarNode('private_key')->end()
                ->end()
            ->end()
            ->arrayNode('loggers')
                ->isRequired()
                ->children()
                    ->arrayNode('psr_logger')
                        ->canBeUnset()
                        ->children()
                            ->booleanNode('enabled')->defaultFalse()->end()
                            ->booleanNode('encrypted')->defaultFalse()->end()
                            ->booleanNode('log_pii')->defaultFalse()->end()
                        ->end()
                    ->end()
                    ->arrayNode('doctrine_logger')
                        ->canBeUnset()
                        ->children()
                            ->booleanNode('enabled')->defaultFalse()->end()
                            ->booleanNode('encrypted')->defaultFalse()->end()
                            ->booleanNode('log_pii')->defaultFalse()->end()
                        ->end()
                    ->end()
                    ->arrayNode('file_logger')
                        ->canBeUnset()
                        ->children()
                            ->booleanNode('enabled')->defaultFalse()->end()
                            ->booleanNode('encrypted')->defaultFalse()->end()
                            ->booleanNode('log_pii')->defaultFalse()->end()
                            ->scalarNode('path')->end()
                        ->end()
                    ->end()
                    ->arrayNode('rabbitmq_logger')
                        ->canBeUnset()
                        ->children()
                            ->booleanNode('enabled')->defaultFalse()->end()
                            ->booleanNode('encrypted')->defaultFalse()->end()
                            ->booleanNode('log_pii')->defaultFalse()->end()
                            ->scalarNode('producer_service')->isRequired()->end()
                            ->scalarNode('routing_key')->defaultValue("")->end()
                            ->arrayNode('additional_events')
                                ->beforeNormalization()->castToArray()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('Resources/config/services.yml');

        if (isset($config['loggers']['psr_logger'])) {
            $builder->getDefinition(PsrLogger::class)->setArgument(2, $config['loggers']['psr_logger']['log_pii']);

            // Create and add encryption class
            $definition = new Definition(EncryptionHandler::class);
            $definition->setArgument(0, $config['loggers']['psr_logger']['encrypted']);
            $definition->setArgument(1, $config['encryption']['public_key']);
            $definition->setArgument(2, $config['encryption']['private_key']);
            $builder->getDefinition(PsrLogger::class)->setArgument(0, $definition);

            $definition = $builder->getDefinition(AuditLogger::class);
            $definition->addMethodCall('addLogger', [new Reference(PsrLogger::class)]);
        }

        if (isset($config['loggers']['file_logger'])) {
            $builder->getDefinition(FileLogger::class)->setArgument(1, $config['loggers']['file_logger']['path']);
            $builder->getDefinition(FileLogger::class)->setArgument(2, $config['loggers']['file_logger']['log_pii']);

            // Create and add encryption class
            $definition = new Definition(EncryptionHandler::class);
            $definition->setArgument(0, $config['loggers']['file_logger']['encrypted']);
            $definition->setArgument(1, $config['encryption']['public_key']);
            $definition->setArgument(2, $config['encryption']['private_key']);
            $builder->getDefinition(FileLogger::class)->setArgument(0, $definition);

            $definition = $builder->getDefinition(AuditLogger::class);
            $definition->addMethodCall('addLogger', [new Reference(FileLogger::class)]);
        }

        if (isset($config['loggers']['doctrine_logger'])) {
            $builder
                ->getDefinition(DoctrineLogger::class)
                ->setArgument(2, $config['loggers']['doctrine_logger']['log_pii']);

            // Create and add encryption class
            $definition = new Definition(EncryptionHandler::class);
            $definition->setArgument(0, $config['loggers']['doctrine_logger']['encrypted']);
            $definition->setArgument(1, $config['encryption']['public_key']);
            $definition->setArgument(2, $config['encryption']['private_key']);
            $builder->getDefinition(DoctrineLogger::class)->setArgument(0, $definition);

            $definition = $builder->getDefinition(AuditLogger::class);
            $definition->addMethodCall('addLogger', [new Reference(DoctrineLogger::class)]);
        }

        if (isset($config['loggers']['rabbitmq_logger'])) {
            if (!class_exists(OldSoundRabbitMqBundle::class, false)) {
                throw new \Exception(
                    'RabbitMQ logger is configured to log data, but the RabbitMQ bundle is not installed. '
                    . 'Please try and run "composer require php-amqplib/rabbitmq-bundle"',
                );
            }

            // Create and add encryption class
            $definition = new Definition(EncryptionHandler::class);
            $definition->setArgument(0, $config['loggers']['rabbitmq_logger']['encrypted']);
            $definition->setArgument(1, $config['encryption']['public_key']);
            $definition->setArgument(2, $config['encryption']['private_key']);
            $builder->getDefinition(RabbitmqLogger::class)->setArgument(0, $definition);

            $builder
                ->getDefinition(RabbitmqLogger::class)
                ->setArgument(1, new Reference($config['loggers']['rabbitmq_logger']['producer_service']));
            $builder
                ->getDefinition(RabbitmqLogger::class)
                ->setArgument(2, $config['loggers']['rabbitmq_logger']['routing_key']);
            $builder
                ->getDefinition(RabbitmqLogger::class)
                ->setArgument(3, $config['loggers']['rabbitmq_logger']['additional_events']);

            $definition = $builder->getDefinition(AuditLogger::class);
            $definition->addMethodCall('addLogger', [new Reference(RabbitmqLogger::class)]);
        }
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $mappings = [
            realpath(__DIR__ . '/Resources/config/doctrine') => 'MinVWS\AuditLoggerBundle\Entity',
        ];

        $container->addCompilerPass(DoctrineOrmMappingsPass::createYamlMappingDriver($mappings));
    }

    public function getAlias(): string
    {
        return 'audit_logger';
    }
}
