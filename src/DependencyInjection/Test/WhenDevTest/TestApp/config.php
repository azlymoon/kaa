<?php

use Kaa\CodeGen\Config\PhpConfig;
use Kaa\DependencyInjection\DependencyInjectionGenerator;

return new PhpConfig(
    [
        new DependencyInjectionGenerator(),
    ],
    [
        'kernel_dir' => __DIR__,
        'code_gen_namespace' => 'Kaa\DependencyInjection\Test\Generated\WhenDevTest',
        'code_gen_directory' => dirname(__DIR__, 2) . '/Generated/WhenDevTest',
        'service' => [
            'namespaces' => ['Kaa\DependencyInjection\Test\WhenDevTest\TestApp'],
        ]
    ]
);
