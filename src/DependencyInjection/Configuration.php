<?php

declare(strict_types=1);

namespace MinVWS\AuditLoggerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        return (new TreeBuilder('audit_logger'))
            ->getRootNode()
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
}
