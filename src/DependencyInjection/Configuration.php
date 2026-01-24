<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('mdx_htmx');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('htmx_only')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultTrue()
                            ->info('Enable or disable the #[HtmxOnly] attribute functionality')
                        ->end()
                        ->integerNode('status_code')
                            ->defaultValue(404)
                            ->info('HTTP status code to return for non-htmx requests (404, 403, 400)')
                        ->end()
                        ->scalarNode('message')
                            ->defaultValue('Not Found')
                            ->info('Error message for non-htmx requests')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('default_view_data')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultTrue()
                            ->info('Add _htmx_result and _htmx_request variables to views')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('response')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('vary_header')
                            ->defaultTrue()
                            ->info('Add "Vary: HX-Request" header to responses (important for caching)')
                        ->end()
                        ->booleanNode('strict_mode')
                            ->defaultFalse()
                            ->info('Throw exception when HtmxResponse is returned for non-htmx request')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('csrf')
                    ->canBeDisabled()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('token_id')
                            ->defaultValue('mdx-htmx')
                            ->info('CSRF token ID used for generation and validation')
                        ->end()
                        ->scalarNode('header_name')
                            ->defaultValue('X-CSRF-Token')
                            ->info('HTTP header name for CSRF token')
                        ->end()
                        ->arrayNode('safe_methods')
                            ->defaultValue(['GET', 'HEAD', 'OPTIONS'])
                            ->scalarPrototype()->end()
                            ->info('HTTP methods that do not require CSRF validation')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
