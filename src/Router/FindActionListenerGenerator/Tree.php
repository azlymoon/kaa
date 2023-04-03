<?php

declare(strict_types=1);

namespace Kaa\Router\FindActionListenerGenerator;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\Router\Exception\EmptyPathException;
use Kaa\Router\Exception\PathAlreadyExistsException;

#[PhpOnly]
class Tree implements TreeInterface
{
    /** @var TreeNode[] */
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
        $nodes = self::parse($path); // Разбиваем строку на массив
        if (count($nodes) === 0) {
            throw new EmptyPathException();
        }
        $keys = [];
        for ($i = 0; $i < count($nodes); $i++) { // Каждую переменную заменяем {} саму переменную кладём в массив keys
            if (str_contains($nodes[$i], '{')) {
                $keys[$i] = $nodes[$i];
                $nodes[$i] = '{}';
            }
        }
        if (!empty($this->realisedElements[0][$method])) { // Проверка на наличии в head элемента определённого метода
            $existPath = implode('/', [$method, ...$nodes]); // Создаём полный путь с методом
            if (!empty($this->realisedElements[count($nodes)][$existPath])) { // Проверяем нет ли похожего путя в дереве
                /** @var TreeNode $realisedElement */
                $realisedElement = $this->realisedElements[count($nodes)][$existPath];
                if ($realisedElement->getName() === null) { // Если похожий путь есть, но он ни к чему не ведёт - меняем
                    $realisedElement->setName($name);
                    $realisedElement->setKeys($keys);
                    return;
                }
                if ($realisedElement->getName() === $name) { // Если есть путь с таким же именем - ничего не делаем
                    return;
                }
                throw new PathAlreadyExistsException(sprintf(
                    'Path "%s" with different name already exists!',
                    $path
                )); // Если такой же путь, но с другим именем - выдаём ошибку
            }
        } else { // Если такого метода нет - создаём и добавляем в head
            $prom = new TreeNode($method);
            $this->head[] = $prom;
            $this->realisedElements[0][$method] = $prom;
        }
        $prevKey = $method; // Переменная содержащая путь к определённой ноде
        for ($i = 0; $i < count($nodes) - 1; $i++) {
            if (empty($this->realisedElements[$i + 1]["$prevKey/$nodes[$i]"])) { // Если нет такой ноды уже - добавляем
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
    private static function parse(string $path): array
    {
        $mas = explode('/', trim($path, '/'));
        if ($mas[0] === '') {
            array_shift($mas);
        }
        return $mas;
    }
}
