<?php

declare(strict_types=1);

namespace Kaa\Router\Contract;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\Contract\NewInstanceGeneratorInterface;

#[PhpOnly]
class DefaultNewInstanceGenerator implements NewInstanceGeneratorInterface
{
    public function getNewInstanceCode(string $varName, string $className): string
    {
        return sprintf('$%s = new \%s();', $varName, $className);
    }
}
