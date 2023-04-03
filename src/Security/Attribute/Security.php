<?php

declare(strict_types=1);

namespace Kaa\Security\Attribute;

use Attribute;
use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\Router\Interceptor\Interceptor;
use Kaa\Router\Interceptor\InterceptorType;
use Kaa\Security\InterceptorGenerator\SecurityInterceptorGenerator;

#[PhpOnly]
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
readonly class Security extends Interceptor
{
    /**
     * @param string[] $roles
     */
    public function __construct(array $roles)
    {
        parent::__construct(new SecurityInterceptorGenerator($roles), InterceptorType::BEFORE);
    }
}
