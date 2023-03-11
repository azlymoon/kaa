<?php


declare(strict_types=1);

namespace Kaa\Router\FindActionListenerGenerator;
interface TreeNodeInterface{
    public function getData(): string;
    public function getNext(): array;
    public function getName(): ?string;
    public function getKeys(): array;
    public function setName(string $name);
    public function addNext(TreeNode $nextNode);
}