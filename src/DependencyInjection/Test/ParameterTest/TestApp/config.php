<?php

use Kaa\CodeGen\Config\PhpConfig;
use Kaa\DependencyInjection\DependencyInjectionGenerator;

return new PhpConfig(
    [
        new DependencyInjectionGenerator(),
    ],
    [
        'kernel_dir' => __DIR__,
        'code_gen_namespace' => 'Kaa\DependencyInjection\Test\Generated\ParameterTest',
        'code_gen_directory' => dirname(__DIR__, 2) . '/Generated/ParameterTest',
        'service' => [
            'namespaces' => ['Kaa\DependencyInjection\Test\ParameterTest\TestApp'],
            'parameters' => [
                'param' => [
                    'value' => 'param_value'
                ],
                'env_param' => [
                    'value' => '%env(param)%'
                ],
                'binded_param' => [
                    'value' => 1,
                    'binding' => 'int $bindedParam'
                ]
            ]
        ]
    ]
);
