<?php

use Kaa\CodeGen\Config\PhpConfig;
use Kaa\DependencyInjection\DependencyInjectionGenerator;

return new PhpConfig(
    [
        new DependencyInjectionGenerator(),
    ],
    [
        'kernel_dir' => __DIR__,
        'code_gen_namespace' => 'Kaa\DependencyInjection\Test\Generated\WhenThrowsTest',
        'code_gen_directory' => dirname(__DIR__, 2) . '/Generated/WhenThrowsTest',
        'service' => [
            'namespaces' => ['Kaa\DependencyInjection\Test\WhenThrowsTest\TestApp'],
        ]
    ]
);
