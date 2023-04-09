<?php

namespace Kaa\Router\ActionFinder;
use Kaa\Router\Action;
use Kaa\Router\Attribute\Delete;
use Kaa\Router\Attribute\Get;
use Kaa\Router\Attribute\Head;
use Kaa\Router\Attribute\Patch;
use Kaa\Router\Attribute\Post;
use Kaa\Router\Attribute\Put;
use Kaa\Router\Attribute\Route;
use Kaa\Router\Exception\YamlParseException;
use ReflectionException;

class YamlActionFinder implements ActionFinderInterface
{
    public function find(array $userConfig): array
    {
        $actions = [];
        foreach ($userConfig as $name => $data) {
            if (
                !in_array(
                    $name,
                    ["router", "service", "kernel_dir", "code_gen_namespace", "code_gen_directory"],
                    true
                )
            ) {
                $actions[] = $this->buildAction($name, $data);
            }
        }
        return $actions;
    }

    /**
     * @throws YamlParseException
     * @throws ReflectionException
     */
    private function buildAction(string $routeName, array $data): Action
    {
        if ($data["controller"] === null) {
            throw new YamlParseException("Controller can't be null");
        }
        $methodInfo = $this->parseClassString($data["controller"]);
        $routes = $this->generateRoutes($routeName, $data);
        return new Action(
            new \ReflectionMethod($methodInfo[0], $methodInfo[1]),
            new \ReflectionClass($methodInfo[0]),
            $routes,
            [],
            [],
            []
        );
    }

    /**
     * @return string[]
     */
    private function parseClassString(string $classString): array
    {
        return array_filter(explode("::", $classString));
    }

    /**
     * @param string $name
     * @param array $data
     * @return Route[]
     * @throws YamlParseException
     */
    private function generateRoutes(string $name, array $data): array
    {
        if ($data["path"] === null) {
            throw new YamlParseException("Path can't be empty");
        }
        if ($data["methods"] === null) {
            return [
                new Get($data["path"], $name),
                new Post($data["path"], $name),
                new Put($data["path"], $name),
                new Patch($data["path"], $name),
                new Delete($data["path"], $name),
                new Head($data["path"], $name)];
        } else {
            $routes = [];
            $methods = explode("|", $data["path"]);
            foreach ($methods as $method) {
                $routes[] = match ($method) {
                    "GET" => new Get($data["path"], $name),
                    "POST" => new Post($data["path"], $name),
                    "PUT" => new Put($data["path"], $name),
                    "PATCH" => new Patch($data["path"], $name),
                    "DELETE" => new Delete($data["path"], $name),
                    "HEAD" => new Head($data["path"], $name),
                    default => throw new YamlParseException(sprintf("Invalid method %s", $method)),
                };
            }
            return $routes;
        }
    }
}
