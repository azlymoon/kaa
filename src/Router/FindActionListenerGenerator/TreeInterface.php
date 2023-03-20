<?php

declare(strict_types=1);

namespace Kaa\Router\FindActionListenerGenerator;

use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
interface TreeInterface
{
    /** @return TreeNodeInterface[] */
    public function getHead(): array;

    public function addElement(string $path, string $name, string $method): void;
}
