<?php

/**
 * AssetMapper importmap configuration.
 * Run "php bin/console importmap:require <package>" to add packages.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    'htmx.org' => [
        'version' => '2.0.4',
    ],
];
