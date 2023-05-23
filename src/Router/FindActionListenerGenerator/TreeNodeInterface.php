<?php

declare(strict_types=1);

namespace Kaa\Router\FindActionListenerGenerator;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\Router\CallableRoute;

#[PhpOnly]
interface TreeNodeInterface
{
    public function getData(): string;

    /** @return TreeNodeInterface[] */
    public function getNext(): array;

    public function getName(): ?string;

    /** @return string[]|null */
    public function getKeys(): ?array;

    /** @param string[]|null $keys */
    public function setKeys(?array $keys): void;

    public function setName(string $name): void;

    public function addNext(TreeNode $nextNode): void;

    public function setRoute(CallableRoute $route): void;

    public function getRoute(): ?CallableRoute;
}
