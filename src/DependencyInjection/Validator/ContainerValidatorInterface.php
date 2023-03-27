<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Validator;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\DependencyInjection\Collection\Container;
use Kaa\DependencyInjection\Exception\BadDefinitionException;

#[PhpOnly]
interface ContainerValidatorInterface
{
    /**
     * @throws BadDefinitionException
     */
    public function validate(Container $container): void;
}
