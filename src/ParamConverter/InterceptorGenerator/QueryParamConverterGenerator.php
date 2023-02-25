<?php

declare(strict_types=1);

namespace Kaa\ParamConverter\InterceptorGenerator;

use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
readonly class QueryParamConverterGenerator extends AbstractSetterParamConverterGenerator
{
    protected function getGetParameterMethodName(): string
    {
        return 'queryParam';
    }
}
