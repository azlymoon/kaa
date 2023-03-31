<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\NoConfigTest\TestApp\Model;

class NotAService
{
    public function __construct(string $a)
    {
    }
}
