<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('mdx_htmx', [
        'htmx_only' => [
            'enabled' => true,
            'status_code' => 404,
            'message' => 'Not Found',
        ],
        'default_view_data' => [
            'enabled' => true,
        ],
        'response' => [
            'vary_header' => true,
            'strict_mode' => false,
        ],
        'csrf' => [
            'enabled' => true,
        ],
    ]);
};
