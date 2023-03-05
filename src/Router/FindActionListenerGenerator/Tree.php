<?php

declare(strict_types=1);
namespace Kaa\Router\FindActionListenerGenerator;

use Kaa\Router\Exception\PathAlreadyExistsException;
use Kaa\Router\Exception\EmptyPathException;

class Tree implements TreeInterface{

    private array $head;
    private array $existsPaths;
    private array $realisedElements;

    public function __construct()
    {
        $this->head = [];
        $this->realisedElements = [];
    }

    public function getHead() : array
    {
        return $this->head;
    }

    /**
     * @throws EmptyPathException
     * @throws PathAlreadyExistsException
     */
    public function addElement(string $path, string $name, string $method)
    {
        $nodes = self::parse($path);
        if (count($nodes) == 0){
            throw new EmptyPathException();
        }
        self::replacingCheck([$method, ...$nodes]);
        if (!empty($this->realisedElements[0][$method])){
            $a = implode("/", $nodes);
            $a = "$method/".$a;
            if (!empty($this->realisedElements[count($nodes)][$a])){
                /** @var TreeNode $realisedElement */
                $realisedElement = $this->realisedElements[count($nodes)][$a];
                if (!$realisedElement->getName()){
                    $realisedElement->setName($name);
                    return;
                }
                if ($realisedElement->getName() === $name){
                    return;
                }
                throw new PathAlreadyExistsException();
            }
        } else{
            $prom = new TreeNode($method);
            $this->head[] = $prom;
            $this->realisedElements[0][$method] = $prom;
        }
        $prevKey = $method;
        for($i = 0; $i < count($nodes) - 1; $i++){
            if (empty($this->realisedElements[$i+1]["$prevKey/$nodes[$i]"])){
                $prom = new TreeNode($nodes[$i]);
                /** @var TreeNode $realisedElement*/
                $realisedElement = $this->realisedElements[$i][$prevKey];
                $realisedElement->addNext($prom);
                $this->realisedElements[$i + 1]["$prevKey/$nodes[$i]"] = $prom;
            }
            $prevKey = "$prevKey/$nodes[$i]";
        }
        $prom = new TreeNode($nodes[count($nodes) - 1], $name);
        /** @var TreeNode $realisedElement*/
        $realisedElement = $this->realisedElements[count($nodes) - 1][$prevKey];
        $realisedElement->addNext($prom);
        $this->realisedElements[count($nodes)]["$prevKey/{$nodes[count($nodes) - 1]}"] = $prom;
    }

    private function parse(string $path): array
    {
        $mas = explode("/", $path);
        if ($mas[0] == ""){
            unset($mas[0]);
            $mas = array_values($mas);
        }
        return $mas;
    }

    /**
     * @throws PathAlreadyExistsException
     */
    private function replacingCheck(array $nodes){
        $isKeysSame = true;
        $isExists = false;
        foreach ($this->existsPaths as $item){
            if (count($nodes) == count($item)){
                $i = 0;
                for (; $i < count($item); $i++){
                    if ((strpos($nodes[$i], "{") !== false) and (strpos($item[$i], "{") !== false)){
                        if ($nodes[$i] !== $item[$i]){
                            $isKeysSame = false;
                        }
                    }
                    else{
                        if ($nodes[$i] !== $item[$i]) {
                            break;
                        }
                    }
                }
                if ($i == count($item)){
                    $isExists = true;
                    break;
                }
            }
        }
        if ($isExists){
            if (!$isKeysSame){
                throw new PathAlreadyExistsException();
            }
        }
        else{
            $this->existsPaths[] = $nodes;
        }
    }
}