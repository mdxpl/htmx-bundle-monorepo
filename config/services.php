<?php

use Mdxpl\HtmxBundle\Controller\ArgumentResolver\HtmxResponseValueResolver;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container
        ->services()
            ->set('mdxpl.htmx.response.value.resolver', HtmxResponseValueResolver::class)
                ->tag('controller.argument_value_resolver', ['priority' => 100]);
};
