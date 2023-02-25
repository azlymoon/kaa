<?php

declare(strict_types=1);

namespace Kaa\Router\Attribute;

use Attribute;
use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\Router\HttpMethod;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
#[PhpOnly]
readonly class Delete extends Route
{
    public function __construct(string $route, ?string $name = null)
    {
        parent::__construct($route, HttpMethod::DELETE, $name);
    }
}
