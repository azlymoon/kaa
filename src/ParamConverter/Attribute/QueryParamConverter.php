<?php

declare(strict_types=1);

namespace Kaa\ParamConverter\Attribute;

use Attribute;
use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\ParamConverter\InterceptorGenerator\QueryParamConverterGenerator;
use Kaa\Router\Interceptor\Interceptor;
use Kaa\Router\Interceptor\InterceptorType;

#[PhpOnly]
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
readonly class QueryParamConverter extends Interceptor
{
    public function __construct(string $modelName)
    {
        parent::__construct(new QueryParamConverterGenerator($modelName), InterceptorType::BEFORE);
    }
}
