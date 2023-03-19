<?php

declare(strict_types=1);

namespace Kaa\Router\FindActionListenerGenerator;

use Kaa\Router\Exception\EmptyPathException;
use Kaa\Router\Exception\PathAlreadyExistsException;

class Tree implements TreeInterface
{
    /** @var TreeNode[] $head */
    private array $head;
    /** @var TreeNode[][] */
    private array $realisedElements;

    public function __construct()
    {
        $this->head = [];
        $this->realisedElements = [];
    }

    public function getHead(): array
    {
        return $this->head;
    }

    /**
     * @throws EmptyPathException
     * @throws PathAlreadyExistsException
     */
    public function addElement(string $path, string $name, string $method): void
    {
        $nodes = self::parse($path);
        if (count($nodes) === 0) {
            throw new EmptyPathException();
        }
        $keys = [];
        for ($i = 0; $i < count($nodes); $i++) {
            if (str_contains($nodes[$i], '{')) {
                $keys[$i] = $nodes[$i];
                $nodes[$i] = '{}';
            }
        }
        if (!empty($this->realisedElements[0][$method])) {
            $a = implode('/', [$method, ...$nodes]);
            if (!empty($this->realisedElements[count($nodes)][$a])) {
                /** @var TreeNode $realisedElement */
                $realisedElement = $this->realisedElements[count($nodes)][$a];
                if ($realisedElement->getName() === null) {
                    $realisedElement->setName($name);
                    $realisedElement->setKeys($keys);
                    return;
                }
                if ($realisedElement->getName() === $name) {
                    return;
                }
                throw new PathAlreadyExistsException($path);
            }
        } else {
            $prom = new TreeNode($method);
            $this->head[] = $prom;
            $this->realisedElements[0][$method] = $prom;
        }
        $prevKey = $method;
        for ($i = 0; $i < count($nodes) - 1; $i++) {
            if (empty($this->realisedElements[$i + 1]["$prevKey/$nodes[$i]"])) {
                $prom = new TreeNode($nodes[$i]);
                /** @var TreeNode $realisedElement */
                $realisedElement = $this->realisedElements[$i][$prevKey];
                $realisedElement->addNext($prom);
                $this->realisedElements[$i + 1]["$prevKey/$nodes[$i]"] = $prom;
            }
            $prevKey = "$prevKey/$nodes[$i]";
        }
        $prom = new TreeNode($nodes[count($nodes) - 1], $name, $keys);
        /** @var TreeNode $realisedElement */
        $realisedElement = $this->realisedElements[count($nodes) - 1][$prevKey];
        $realisedElement->addNext($prom);
        $this->realisedElements[count($nodes)]["$prevKey/{$nodes[count($nodes) - 1]}"] = $prom;
    }
    /** @return string[] */
    private function parse(string $path): array
    {
        $mas = explode('/', trim($path, '/'));
        if ($mas[0] === '') {
            array_shift($mas);
        }
        return $mas;
    }
}
