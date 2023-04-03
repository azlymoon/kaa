<?php

use Kaa\CodeGen\Config\PhpConfig;
use Kaa\DependencyInjection\DependencyInjectionGenerator;

return new PhpConfig(
    [
        new DependencyInjectionGenerator(),
    ],
    [
        'kernel_dir' => __DIR__,
        'code_gen_namespace' => 'Kaa\DependencyInjection\Test\Generated\WhenProdTest',
        'code_gen_directory' => dirname(__DIR__, 2) . '/Generated/WhenProdTest',
        'service' => [
            'namespaces' => ['Kaa\DependencyInjection\Test\WhenProdTest\TestApp'],
        ]
    ]
);
