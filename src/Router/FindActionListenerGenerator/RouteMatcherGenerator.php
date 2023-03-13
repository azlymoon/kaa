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
        foreach ($routes as $route) {
            $mathTree->addElement($route->path, $route->name, $route->method);
        }
        $code[] = '$Router_route_name = null;';
        $code[] = '$matches = [];';
        $code[] = '$nodes = explode("/", $route);';
        $code[] = 'if ($nodes[0] === ""){';
        $code[] = '    unset($nodes[0]);';
        $code[] = '    $nodes = array_values($nodes);';
        $code[] = '}';
        $code[] = '$count_nodes = count($nodes);';
        foreach ($mathTree->getHead() as $headItem) {
            $code[] = sprintf(
                'if ($method === "%s"){',
                $headItem->getData()
            );
            $depth = 0;
            $parents[$depth] = $headItem->getNext();
            $indexes[$depth] = 0;

            while (true) {
                if ($indexes[$depth] < count($parents[$depth])) {
                    $t = $parents[$depth][$indexes[$depth]];
                    if ($t->getName() !== null) {
                        if (!str_contains($t->getData(), "{")) {
                            $code[] = str_repeat("\t", $depth + 1) . sprintf(
                                'if(($nodes[%d] === "%s") && ($count_nodes === %d)){',
                                $depth,
                                $t->getData(),
                                $depth + 1
                            );
                        } else {
                            $code[] = str_repeat("\t", $depth + 1) . sprintf(
                                'if($count_nodes === %d){',
                                $depth + 1
                            );
                        }
                        $code[] = str_repeat("\t", $depth + 2) . sprintf(
                            '$Router_route_name = "%s";',
                            $t->getName()
                        );
                        if ($t->getKeys() !== null) {
                            foreach ($t->getKeys() as $k => $v) {
                                $code[] = str_repeat("\t", $depth + 2) . sprintf(
                                    '$matches["%s"] = $nodes[%d];',
                                    $v,
                                    $k
                                );
                            }
                        }
                        $code[] = str_repeat("\t", $depth + 1) . '}';
                    }
                    if (!empty($t->getNext())) {
                        if (!str_contains($t->getData(), '{')) {
                            if ($indexes[$depth] === 0) {
                                $code[] = str_repeat("\t", $depth + 1) .
                                    sprintf(
                                        'if (($nodes[%d] === "%s") && ($count_nodes >= %d)){',
                                        $depth,
                                        $parents[$depth][$indexes[$depth]]->getData(),
                                        $depth + 1
                                    );
                            } else {
                                $code[] = str_repeat("\t", $depth + 1) . sprintf(
                                    'else if (($nodes[%d] === "%s") && ($count_nodes >= %d)){',
                                    $depth,
                                    $parents[$depth][$indexes[$depth]]->getData(),
                                    $depth + 1
                                );
                            }
                        } else {
                            if ($indexes[$depth] === 0) {
                                $code[] = str_repeat("\t", $depth + 1) . sprintf(
                                    'if ($count_nodes >= %d){',
                                    $depth + 1
                                );
                            } else {
                                $code[] = str_repeat("\t", $depth + 1) . sprintf(
                                    'else if ($count_nodes >= %d){',
                                    $depth + 1
                                );
                            }
                        }

                        $indexes[$depth]++;
                        $depth++;
                        $indexes[$depth] = 0;
                        $parents[$depth] = $parents[$depth - 1][$indexes[$depth - 1] - 1]->getNext();
                    } else {
                        $indexes[$depth]++;
                    }
                } else {
                    $code[] = str_repeat("\t", $depth) . '}';
                    if ($depth === 0) {
                        break;
                    } else {
                        unset($parents[$depth]);
                        $depth--;
                    }
                }
            }
        }
        return implode("\n", $code);
    }
}
