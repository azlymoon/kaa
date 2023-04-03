<?php

use Kaa\CodeGen\Config\PhpConfig;
use Kaa\DependencyInjection\DependencyInjectionGenerator;
use Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\DynamicFactory\DynamicFactory;
use Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\DynamicFactory\NamedDynamicFactory;
use Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\DynamicFactory\ServiceWithClassDynamicFactory;
use Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\DynamicFactory\ServiceWithNamedDynamicFactory;
use Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\Named\FirstNamedImplementation;
use Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\Named\SecondNamedImplementation;
use Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\StaticFactory\ServiceWithStaticFactory;
use Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\StaticFactory\StaticFactory;
use Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\When\WhenImplementationDev;
use Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\When\WhenImplementationProd;
use Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\WhenFactory\AbstractService;
use Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\WhenFactory\DefaultFactory;
use Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\WhenFactory\DevFactory;
use Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\WhenFactory\ProdFactory;
use Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\SuperService;

return new PhpConfig(
    [
        new DependencyInjectionGenerator(),
    ],
    [
        'kernel_dir' => __DIR__,
        'code_gen_namespace' => 'Kaa\DependencyInjection\Test\Generated\ArrayConfigTest',
        'code_gen_directory' => dirname(__DIR__, 2) . '/Generated/ArrayConfigTest',
        'service' => [
            'namespaces' => ['Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp'],

            ServiceWithClassDynamicFactory::class => [
                'factories' => [
                    [
                        'factory' => DynamicFactory::class,
                        'method' => 'create',
                    ]
                ]
            ],

            'factory.named' => [
                'class' => NamedDynamicFactory::class
            ],

            ServiceWithNamedDynamicFactory::class => [
                'factories' => [
                    [
                        'factory' => 'factory.named',
                    ]
                ]
            ],

            'first' => [
                'class' => FirstNamedImplementation::class,
            ],

            SecondNamedImplementation::class => [
                'name' => 'second',
            ],

            ServiceWithStaticFactory::class => [
                'factories' => [
                    [
                        'factory' => StaticFactory::class,
                        'method' => 'create',
                        'static' => true,
                    ]
                ]
            ],

            WhenImplementationDev::class => [
                'when' => ['dev', 'test']
            ],

            WhenImplementationProd::class => [
                'when' => 'prod'
            ],

            AbstractService::class => [
                'factories' => [
                    [
                        'factory' => ProdFactory::class,
                        'when' => 'prod',
                    ],
                    [
                        'factory' => DevFactory::class,
                        'when' => 'dev'
                    ],
                    [
                        'factory' => DefaultFactory::class
                    ]
                ]
            ],

            SuperService::class => [
                'dependencies' => [
                    'firstNamed' => 'first',
                    'secondNamed' => 'second'
                ]
            ]
        ]
    ]
);
