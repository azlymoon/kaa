<?php

declare(strict_types=1);

namespace Kaa\Router\FindActionListenerGenerator;

use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
class TreeNode implements TreeNodeInterface
{
    private ?string $name;

    /** @var string[]|null $keys */
    private ?array $keys;
    private string $data;

    /** @var TreeNode[] $next */
    private array $next;

    /**
     * @param string $data
     * @param string|null $name
     * @param string[]|null $keys
     */
    public function __construct(string $data, ?string $name = null, ?array $keys = null)
    {
        $this->data = $data;
        $this->name = $name;
        $this->next = [];
        $this->keys = $keys;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return ?string[]
     */
    public function getKeys(): ?array
    {
        return $this->keys;
    }

    /** @param ?string[] $keys */
    public function setKeys(?array $keys): void
    {
        $this->keys = $keys;
    }
    public function getData(): string
    {
        return $this->data;
    }
    /** @return TreeNode[] */
    public function getNext(): array
    {
        return $this->next;
    }
    public function addNext(TreeNode $nextNode): void
    {
        if (str_contains($nextNode->data, '{')) {
            $this->next[] = $nextNode;
        } else {
            array_unshift($this->next, $nextNode);
        }
    }
}
