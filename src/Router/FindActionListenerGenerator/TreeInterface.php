<?php

declare(strict_types=1);

namespace Kaa\Router\FindActionListenerGenerator;

interface TreeInterface
{
    /** @return TreeNodeInterface[] */
    public function getHead(): array;
    public function addElement(string $path, string $name, string $method): void;
}
