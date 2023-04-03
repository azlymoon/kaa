<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\WhenFactory;

class ProdFactory
{
    public function __invoke(): ProdService
    {
        return new ProdService();
    }
}
