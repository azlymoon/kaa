<?php

declare(strict_types=1);

namespace Kaa\Router\FindActionListenerGenerator;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\ProvidedDependencies;
use Kaa\Router\Exception\EmptyPathException;
use Kaa\Router\Exception\PathAlreadyExistsException;

#[PhpOnly]
class RouteMatcherGenerator implements RouteMatcherGeneratorInterface
{
    /**
     * @throws EmptyPathException
     * @throws PathAlreadyExistsException
     */
    public function generateMatchCode(
        string $targetVarName,
        string $routeVarName,
        string $methodVarName,
        array $routes,
        array $userConfig,
        ProvidedDependencies $providedDependencies
    ): string {
        $code = [];
        $indexes = [];
        $parents = [];
        $mathTree = new Tree();
        foreach ($routes as $route){
            $mathTree->addElement($route->path, $route->name, $route->method);
        }
        $code[] = '$matches = [];';
        $code[] = '$nodes = explode("/", $route);';
        $code[] = 'if ($nodes[0] == ""){';
        $code[] = '    unset($nodes[0]);';
        $code[] = '    $nodes = array_values($nodes);';
        $code[] = '}';
        foreach ($mathTree->getHead() as $headItem){
            $code[] = sprintf(
                'if ($method === "%s"){',
                $headItem->getData()
            );
            $depth = 0;
            $parents[$depth] = $headItem->getNext();
            $indexes[$depth] = 0;
            while ($parents[$depth]){
                if ($indexes[$depth] < count($parents[$depth])) {
                    if (strpos($parents[$depth][$indexes[$depth]]->getData(), "{") === false) {
                        $code[] = sprintf(
                            str_repeat("\t", $depth+1).'if ($nodes[%d] === "%s"){',
                            $depth,
                            $parents[$depth][$indexes[$depth]]->getData()
                        );
                    } else {
                        if (count($parents[$depth]) > 1) {
                            $code[] = str_repeat("\t", $depth + 1) . 'else {';
                        }
                        $code[] = sprintf(
                            str_repeat("\t", $depth+2).'$matches["%s"] = $nodes[%d];',
                            $parents[$depth][$indexes[$depth]]->getData(),
                            $depth
                        );
                    }
                    if ($parents[$depth][$indexes[$depth]]->getName()) {
                        $code[] = sprintf(
                            str_repeat("\t", $depth+2).'if (count($nodes) == %d) {',
                            $depth + 1
                        );
                        $code[] = sprintf(
                            str_repeat("\t", $depth+3).'$%s = "%s";',
                            $targetVarName,
                            $parents[$depth][$indexes[$depth]]->getName()
                        );
                        $code[] = str_repeat("\t", $depth+2).'}';
                    }
                    if ($parents[$depth][$indexes[$depth]]->getNext()) {
                        $code[] = sprintf(
                            str_repeat("\t", $depth+2).'if (count($nodes) > %d) {',
                            $depth + 1
                        );
                        $depth++;
                        $parents[$depth] = $parents[$depth - 1][$indexes[$depth - 1]]->getNext();
                        $indexes[$depth - 1]++;
                        $indexes[$depth] = 0;
                    } else {
                        // if ($indexes[$depth] == (count($parents[$depth]) - 1)) {
                        $code[] = str_repeat("\t", $depth+1).'}';
                        //}
                        if ($depth>0){
                            $code[] = str_repeat("\t", $depth+1).'}';
                        }
                        $indexes[$depth]++;
                    }

                } else{
                    if (($depth - 1) > -1){
                        $code[] = str_repeat("\t", $depth)."}";
                    }
                    unset($parents[$depth]);
                    $depth--;
                }
            }
            $code[] = '}';
        }
        return implode("\n", $code);
    }
}
