<?php

declare(strict_types=1);

namespace Kaa\Router\FindActionListenerGenerator;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\Router\CallableRoute;

#[PhpOnly]
interface TreeInterface
{
    /** @return TreeNodeInterface[] */
    public function getHead(): array;

    public function addElement(CallableRoute $route): void;
}
