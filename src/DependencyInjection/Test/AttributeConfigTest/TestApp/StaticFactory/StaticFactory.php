<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\AttributeConfigTest\TestApp\StaticFactory;

class StaticFactory
{
    public static bool $wasCalled = false;

    public static function create(): ServiceWithStaticFactory
    {
        self::$wasCalled = true;
        return new ServiceWithStaticFactory();
    }
}
