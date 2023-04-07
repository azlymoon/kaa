<?php

namespace Kaa\Validator\Attribute;

use Attribute;
use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\Router\Interceptor\Interceptor;
use Kaa\Router\Interceptor\InterceptorType;
use Kaa\Validator\GeneratorContext;
use Kaa\Validator\InterceptorGenerator\ValidatorModelGenerator;
use Kaa\Validator\InterceptorGenerator\ValidatorReturnValueGenerator;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
#[PhpOnly]
readonly class ResultValidator extends Interceptor
{
    public function __construct()
    {
        parent::__construct(
            new ValidatorReturnValueGenerator(new ValidatorModelGenerator(new GeneratorContext(), 'postViolationList')),
            InterceptorType::AFTER,
        );
    }
}
