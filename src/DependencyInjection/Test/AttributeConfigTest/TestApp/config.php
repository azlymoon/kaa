<?php

use Kaa\CodeGen\Config\PhpConfig;
use Kaa\DependencyInjection\DependencyInjectionGenerator;

return new PhpConfig(
    [
        new DependencyInjectionGenerator(),
    ],
    [
        'kernel_dir' => __DIR__,
        'code_gen_namespace' => 'Kaa\DependencyInjection\Test\Generated\AttributeConfigTest',
        'code_gen_directory' => dirname(__DIR__, 2) . '/Generated/AttributeConfigTest',
        'service' => [
            'namespaces' => ['Kaa\DependencyInjection\Test\AttributeConfigTest\TestApp'],
        ]
    ]
);
