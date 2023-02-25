<?php

namespace Kaa\Validator\Attribute;

use Attribute;
use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\Router\Interceptor\Interceptor;
use Kaa\Router\Interceptor\InterceptorType;
use Kaa\Validator\GeneratorContext;
use Kaa\Validator\InterceptorGenerator\ValidatorGenerator;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
#[PhpOnly]
readonly class Validator extends Interceptor
{
    public function __construct(string $modelName)
    {
        parent::__construct(
            new ValidatorGenerator($modelName, new GeneratorContext()),
            InterceptorType::BEFORE,
        );
    }
}
