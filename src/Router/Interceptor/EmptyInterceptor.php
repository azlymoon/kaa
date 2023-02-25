<?php

declare(strict_types=1);

namespace Kaa\Router\Interceptor;

use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
readonly class EmptyInterceptor extends Interceptor
{
    public function __construct()
    {
        parent::__construct(new EmptyInterceptorGenerator(), InterceptorType::BEFORE);
    }
}
