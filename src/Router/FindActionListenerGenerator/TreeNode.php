<?php

declare(strict_types=1);

namespace Kaa\Router\FindActionListenerGenerator;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\Router\CallableRoute;

#[PhpOnly]
class TreeNode implements TreeNodeInterface
{
    /** @var TreeNode[] */
    private array $next;

    /**
     * @param string $data
     * @param string|null $name
     * @param string[]|null $keys
     */
    public function __construct(
        private readonly string $data,
        private ?string $name = null,
        private ?array $keys = null,
        private ?CallableRoute $route = null
    ) {
        $this->next = [];
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
     * @return string[]|null
     */
    public function getKeys(): ?array
    {
        return $this->keys;
    }

    /** @param string[]|null $keys */
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

    public function setRoute(CallableRoute $route): void
    {
        $this->route = $route;
    }

    public function getRoute(): ?CallableRoute
    {
        return $this->route;
    }
}
