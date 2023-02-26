<?php
declare(strict_types=1);
namespace Kaa\Router\FindActionListenerGenerator;

class TreeNode implements TreeNodeInterface {

    private ?string $name;
    private string $data;
    private array $next;

    function __construct(string $data, ?string $name = null)
    {
        $this->data = $data;
        $this->name = $name;
        $this->next = [];
    }

    public function setName(string $name){
        $this->name = $name;
    }
    public function getName(): ?string
    {
        return $this->name;
    }
    public function getData() : string
    {
        return $this->data;
    }

    public function getNext() : array
    {
        return $this->next;
    }

    public function addNext(TreeNode $nextNode)
    {
        if (strpos($nextNode->data, "{") !== false){
            $this->next[] = $nextNode;
        } else{
            array_unshift($this->next, $nextNode);
        }
    }
}