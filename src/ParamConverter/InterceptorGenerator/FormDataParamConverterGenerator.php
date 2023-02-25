<?php

declare(strict_types=1);

namespace Kaa\ParamConverter\InterceptorGenerator;

use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
readonly class FormDataParamConverterGenerator extends AbstractSetterParamConverterGenerator
{
    protected function getGetParameterMethodName(): string
    {
        return 'postParam';
    }
}
