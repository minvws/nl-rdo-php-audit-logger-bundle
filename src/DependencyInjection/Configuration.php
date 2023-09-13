<?php

declare(strict_types=1);

namespace MinVWS\AuditLoggerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('audit_logger');

        // Keep compatibility with symfony/config < 4.2
        if (\method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            $rootNode = $treeBuilder->root('audit_logger');
        }

        $rootNode
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
                        ->canBeDisabled()
                        ->children()
                            ->booleanNode('enabled')->defaultFalse()->end()
                            ->booleanNode('encrypted')->defaultFalse()->end()
                            ->booleanNode('log_pii')->defaultFalse()->end()
                        ->end()
                    ->end()
                    ->arrayNode('doctrine_logger')
                        ->canBeDisabled()
                        ->children()
                            ->booleanNode('enabled')->defaultFalse()->end()
                            ->booleanNode('encrypted')->defaultFalse()->end()
                            ->booleanNode('log_pii')->defaultFalse()->end()
                        ->end()
                    ->end()
                    ->arrayNode('file_logger')
                        ->canBeDisabled()
                        ->children()
                            ->booleanNode('enabled')->defaultFalse()->end()
                            ->booleanNode('encrypted')->defaultFalse()->end()
                            ->booleanNode('log_pii')->defaultFalse()->end()
                            ->scalarNode('path')->end()
                        ->end()
                    ->end()
                    ->arrayNode('rabbitmq_logger')
                        ->canBeDisabled()
                        ->children()
                            ->booleanNode('enabled')->defaultFalse()->end()
                            ->booleanNode('encrypted')->defaultFalse()->end()
                            ->booleanNode('log_pii')->defaultFalse()->end()
                            ->arrayNode('additional_events')
                                ->beforeNormalization()->castToArray()->end()
                            ->end()
                        ->end()
                    ->end()

                ->end()
            ->end();

        return $treeBuilder;
    }
}
