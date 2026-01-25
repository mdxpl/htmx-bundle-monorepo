<?php

use App\Twig\SourceCodeExtension;
use App\Twig\ViteExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->autowire()
            ->autoconfigure();

    $services->load('App\\', '../src/')
        ->exclude('../src/Kernel.php');

    $services->load('App\\Controller\\', '../src/Controller/')
        ->tag('controller.service_arguments');

    $services->set(SourceCodeExtension::class)
        ->arg('$projectDir', '%kernel.project_dir%');

    $services->set(ViteExtension::class)
        ->arg('$publicDir', '%kernel.project_dir%/public')
        ->arg('$debug', '%kernel.debug%');
};
