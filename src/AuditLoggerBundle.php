<?php

declare(strict_types=1);

namespace MinVWS\AuditLoggerBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class AuditLoggerBundle extends AbstractBundle
{
    public function build(ContainerBuilder $container)
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
