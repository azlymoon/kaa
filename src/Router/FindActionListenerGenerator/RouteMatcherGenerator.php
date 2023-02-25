<?php

declare(strict_types=1);

namespace Kaa\Router\FindActionListenerGenerator;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\ProvidedDependencies;

#[PhpOnly]
class RouteMatcherGenerator implements RouteMatcherGeneratorInterface
{
    public function generateMatchCode(
        string $targetVarName,
        string $routeVarName,
        string $methodVarName,
        array $routes,
        array $userConfig,
        ProvidedDependencies $providedDependencies
    ): string {
        $code = [];
        $code[] = sprintf(
            'switch ($%s . $%s) {' . "\n",
            $methodVarName,
            $routeVarName,
        );

        foreach ($routes as $route) {
            $routeCode = <<<PHP
case '%s' . '%s':
    $%s = '%s';
    break;
PHP;

            $routeCode = sprintf(
                $routeCode,
                $route->method,
                $route->path,
                $targetVarName,
                $route->name,
            );

            $code[] = $routeCode;
        }

        $code[] = sprintf("default:\n\t$%s = null;\n}", $targetVarName);

        return implode("\n", $code);
    }
}
