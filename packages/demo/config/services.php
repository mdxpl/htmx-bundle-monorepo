<?php

use App\Twig\SourceCodeExtension;
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
};
