<?php

declare(strict_types=1);

namespace Kaa\Router\Contract;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\Contract\InstanceProviderInterface;

#[PhpOnly]
class DefaultInstanceProvider implements InstanceProviderInterface
{
    public function provideInstanceCode(string $className): string
    {
        return sprintf('new \%s();', $className);
    }
}
