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
        foreach ($routes as $route) { // Строим дерево
            $mathTree->addElement($route);
        }
        $code[] = '$Router_route_name = null;'; // Добавляем в код разбиение строки на массив
        $code[] = '$matches = [];';
        $code[] = '$nodes = explode("/", $route);';
        $code[] = 'if ($nodes[0] === ""){';
        $code[] = '    unset($nodes[0]);';
        $code[] = '    $nodes = array_values($nodes);';
        $code[] = '}';
        $code[] = '$count_nodes = count($nodes);';
        foreach ($mathTree->getHead() as $headItem) { // Для каждого метода строим блок if-else
            $code[] = sprintf(
                'if ($method === "%s"){',
                $headItem->getData()
            );
            $depth = 0; // Глубина вложенности
            $parents[$depth] = $headItem->getNext(); // Предыдущие элементы для определённой глубины
            $indexes[$depth] = 0; // Индекс первого предыдущего элемента для которого не построен блок if-else

            while (true) {
                if ($indexes[$depth] < count($parents[$depth])) { // Проверяем что ещё не все блоки для родителей готовы
                    $t = $parents[$depth][$indexes[$depth]];
                    if ($t->getName() !== null) { // Если у ноды есть имя добавляем проверку и присвоение
                        if (!str_contains($t->getData(), "{")) { // Если нода - переменная
                            $code[] = str_repeat("\t", $depth + 1) . sprintf(
                                'if(($nodes[%d] === "%s") && ($count_nodes === %d)){',
                                $depth,
                                $t->getData(),
                                $depth + 1
                            );
                        } else { // Иначе
                            $code[] = str_repeat("\t", $depth + 1) . sprintf(
                                'if($count_nodes === %d){',
                                $depth + 1
                            );
                        }
                        if ($t->getKeys() !== null) { // Записываем в массив все переменные
                            foreach ($t->getKeys() as $k => $v) {
                                $code[] = str_repeat("\t", $depth + 2) . sprintf(
                                    '$matches["%s"] = $nodes[%d];',
                                    $v,
                                    $k
                                );
                            }
                        }
                        $code[] = str_repeat("\t", $depth) . sprintf(
                            "%s",
                            $t->getRoute()->newInstanceCode
                        );
                        $code[] = str_repeat("\t", $depth + 2) . sprintf(
                            <<<'PHP'
        $event->setAction([$%s, '%s']);
PHP,
                            $t->getRoute()->varName,
                            $t->getRoute()->methodName
                        );
                        $code[] = str_repeat("\t", $depth + 2) .
                            '$event->getRequest()->setUrlParams($matches);';
                        $code[] = str_repeat("\t", $depth + 2) .
                        '$event->stopPropagation();';
                        $code[] = str_repeat("\t", $depth + 2) . 'return;';
                        $code[] = str_repeat("\t", $depth + 1) . '}';
                    }
                    if (!empty($t->getNext())) { // Если у текущей ноды есть следующие
                        if (!str_contains($t->getData(), '{')) { // Если это переменная
                            if ($indexes[$depth] === 0) { // Если это первая нода
                                $code[] = str_repeat("\t", $depth + 1) .
                                    sprintf(
                                        'if (($nodes[%d] === "%s") && ($count_nodes >= %d)){',
                                        $depth,
                                        $parents[$depth][$indexes[$depth]]->getData(),
                                        $depth + 1
                                    );
                            } else { // Иначе
                                $code[] = str_repeat("\t", $depth + 1) . sprintf(
                                    'elseif (($nodes[%d] === "%s") && ($count_nodes >= %d)){',
                                    $depth,
                                    $parents[$depth][$indexes[$depth]]->getData(),
                                    $depth + 1
                                );
                            }
                        } else { // Если это не переменная
                            if ($indexes[$depth] === 0) { // Если это случилось на первом элементе
                                $code[] = str_repeat("\t", $depth + 1) . sprintf(
                                    'if ($count_nodes >= %d){',
                                    $depth + 1
                                );
                            } else { // Если это случилось на втором и следующих элементах
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
                    } else { // Если следующих нет
                        $indexes[$depth]++;
                    }
                } else { // Когда все блоки для родительских элементов текущей глубины готовы
                    $code[] = str_repeat("\t", $depth) . '}';
                    if ($depth === 0) { // Заканчиваем как только это произошло для нод из head
                        break;
                    } else { // Поднимаемся на уровень выше в ином случае
                        unset($parents[$depth]);
                        $depth--;
                    }
                }
            }
        }
        return implode("\n", $code);
    }
}
