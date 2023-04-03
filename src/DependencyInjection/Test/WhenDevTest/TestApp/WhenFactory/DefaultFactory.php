<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\WhenDevTest\TestApp\WhenFactory;

class DefaultFactory
{
    public function __invoke(): DefaultService
    {
        return new DefaultService();
    }
}
