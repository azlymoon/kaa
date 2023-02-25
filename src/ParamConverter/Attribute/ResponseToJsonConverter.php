<?php

declare(strict_types=1);

namespace Kaa\ParamConverter\Attribute;

use Attribute;
use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\ParamConverter\InterceptorGenerator\ResponseToJsonConverterGenerator;
use Kaa\Router\Interceptor\Interceptor;
use Kaa\Router\Interceptor\InterceptorType;

#[PhpOnly]
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
readonly class ResponseToJsonConverter extends Interceptor
{
    public function __construct()
    {
        parent::__construct(new ResponseToJsonConverterGenerator(), InterceptorType::AFTER);
    }
}
