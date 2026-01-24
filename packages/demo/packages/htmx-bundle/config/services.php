<?php

use Mdxpl\HtmxBundle\Controller\ArgumentResolver\HtmxResponseValueResolver;
use Mdxpl\HtmxBundle\EventSubscriber\HtmxOnlyAttributeSubscriber;
use Mdxpl\HtmxBundle\EventSubscriber\HtmxRequestSubscriber;
use Mdxpl\HtmxBundle\EventSubscriber\HtmxResponseSubscriber;
use Mdxpl\HtmxBundle\Response\HtmxResponseBuilderFactory;
use Mdxpl\HtmxBundle\Response\ResponseFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;

return static function (ContainerConfigurator $container): void {
    $container
        ->services()
        ->set(HtmxResponseValueResolver::class)
        ->tag('controller.argument_value_resolver', ['priority' => 100])

        ->set(HtmxResponseBuilderFactory::class)
        ->args(['%mdx_htmx.default_view_data.enabled%'])

        ->set(ResponseFactory::class)
        ->args([
            new Reference('twig'),
            '%mdx_htmx.response.vary_header%',
        ])

        ->set(HtmxRequestSubscriber::class)
        ->tag('kernel.event_subscriber')

        ->set(HtmxResponseSubscriber::class)
        ->args([
            new Reference(ResponseFactory::class),
            '%mdx_htmx.response.strict_mode%',
        ])
        ->tag('kernel.event_subscriber')

        ->set(HtmxOnlyAttributeSubscriber::class)
        ->args([
            '%mdx_htmx.htmx_only.enabled%',
            '%mdx_htmx.htmx_only.status_code%',
            '%mdx_htmx.htmx_only.message%',
        ])
        ->tag('kernel.event_subscriber');
};
