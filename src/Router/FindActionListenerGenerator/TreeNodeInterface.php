<?php


declare(strict_types=1);

namespace Kaa\Router\FindActionListenerGenerator;
interface TreeNodeInterface{
    public function getData();
    public function getNext();
    public function getName();
    public function setName(string $name);
    public function addNext(TreeNode $nextNode);
}