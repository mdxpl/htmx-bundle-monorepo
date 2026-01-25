<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Empty_\SimplifyEmptyCheckOnEmptyArrayRector;
use Rector\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Cast\RecastingRemovalRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/packages/htmx-bundle/src',
        __DIR__ . '/packages/demo/src',
    ])
    ->withSkip([
        __DIR__ . '/packages/*/vendor/*',
    ])
    ->withRules([
        // Replace count($array) === 0 with $array === []
        CountArrayToEmptyArrayComparisonRector::class,

        // Simplify empty checks on arrays (e.g., empty($array) -> $array === [])
        SimplifyEmptyCheckOnEmptyArrayRector::class,

        // Remove useless casts (e.g., (string) on already string)
        RecastingRemovalRector::class,
    ]);
