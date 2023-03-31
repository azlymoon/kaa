<?php

use Kaa\CodeGen\Config\PhpConfig;
use Kaa\DependencyInjection\DependencyInjectionGenerator;
use Kaa\DependencyInjection\EventDispatcherLinker\EventDispatcherLinkerGenerator;
use Kaa\EventDispatcher\EventDispatcher;

return new PhpConfig(
    [
        new DependencyInjectionGenerator(),
        new EventDispatcherLinkerGenerator(),
    ],
    [
        'kernel_dir' => __DIR__,
        'code_gen_namespace' => 'Kaa\DependencyInjection\Test\Generated\EventListenerTest',
        'code_gen_directory' => dirname(__DIR__, 2) . '/Generated/EventListenerTest',
        'service' => [
            'namespaces' => ['Kaa\DependencyInjection\Test\EventListenerTest\TestApp'],
            'kernel.dispatcher' => [
                'class' => EventDispatcher::class,
            ]
        ]
    ]
);
