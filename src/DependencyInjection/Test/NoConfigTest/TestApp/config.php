<?php

use Kaa\CodeGen\Config\PhpConfig;
use Kaa\DependencyInjection\DependencyInjectionGenerator;

return new PhpConfig(
    [
        new DependencyInjectionGenerator(),
    ],
    [
        'code_gen_namespace' => 'Kaa\DependencyInjection\Test\Generated\NoConfigTest',
        'code_gen_directory' => dirname(__DIR__, 2) . '/Generated/NoConfigTest',
        'service' => [
            'namespaces' => ['Kaa\DependencyInjection\Test\NoConfigTest\TestApp'],
            'exclude' => ['Kaa\DependencyInjection\Test\NoConfigTest\TestApp\Model'],
        ]
    ]
);
