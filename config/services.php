<?php

use Mdxpl\HtmxBundle\Controller\ArgumentResolver\HtmxResponseValueResolver;
use Mdxpl\HtmxBundle\Controller\HtmxResponseFacade;
use Mdxpl\HtmxBundle\EventSubscriber\HtmxAttributeSubscriber;
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
        ->set(ResponseFactory::class)
        ->args([new Reference('twig')])
        ->set(HtmxResponseFacade::class)
        ->args([
            new Reference(HtmxResponseBuilderFactory::class),
            new Reference(ResponseFactory::class),
        ])
        ->set(HtmxRequestSubscriber::class)
        ->tag('kernel.event_subscriber')
        ->set(HtmxResponseSubscriber::class)
        ->args([new Reference(ResponseFactory::class)])
        ->tag('kernel.event_subscriber')
        ->set(HtmxAttributeSubscriber::class)
        ->tag('kernel.event_subscriber');
};
