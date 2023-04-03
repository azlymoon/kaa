<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\ParameterTest\TestApp;

use Kaa\DependencyInjection\Attribute\Inject;

readonly class SuperService
{
    public function __construct(
        #[Inject('param')] public string $param,
        #[Inject('env_param')] public string $envParam,
        public int $bindedParam
    ) {
    }
}
