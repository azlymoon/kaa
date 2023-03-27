<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\Exception\CodeGenException;
use Kaa\DependencyInjection\Attribute\Inject;
use Kaa\DependencyInjection\Collection\Dependency;
use Kaa\DependencyInjection\Collection\DependencyCollection;
use Kaa\DependencyInjection\Exception\BadDefinitionException;
use ReflectionClass;
use ReflectionNamedType;

#[PhpOnly]
class ReflectionUtils
{
    /**
     * @throws CodeGenException
     */
    public static function getDependencies(ReflectionClass $reflectionClass): DependencyCollection
    {
        $constructor = $reflectionClass->getConstructor();
        if ($constructor === null) {
            return new DependencyCollection();
        }

        $hasInjectedDependencies = false;
        $dependencies = [];
        foreach ($constructor->getParameters() as $parameter) {
            $serviceName = null;

            $injectReflectionAttributes = $parameter->getAttributes(Inject::class);
            if (!empty($injectReflectionAttributes)) {
                /** @var Inject $injectAttribute */
                $injectAttribute = $injectReflectionAttributes[0]->newInstance();
                $serviceName = $injectAttribute->serviceName;
                $hasInjectedDependencies = true;
            }

            $type = $parameter->getType();

            if (!$type instanceof ReflectionNamedType) {
                BadDefinitionException::throw(
                    'Service %s must require service %s only with named type, not a union or an intersection',
                    $reflectionClass->name,
                    $parameter->name
                );
            }

            $dependencies[] = new Dependency($parameter->name, $type->getName(), $serviceName ?? '');
        }

        return new DependencyCollection($dependencies, $hasInjectedDependencies);
    }

    /**
     * @return string[]
     */
    public static function getClassParents(ReflectionClass $reflectionClass): array
    {
        $interfaces = [[$reflectionClass->name]];
        if ($reflectionClass->getParentClass() !== false) {
            $interfaces[] = self::getClassParents($reflectionClass->getParentClass());
        }

        foreach ($reflectionClass->getInterfaces() as $interface) {
            $interfaces[] = self::getClassParents($interface);
        }

        return array_merge(...$interfaces);
    }

    /**
     * @param ReflectionClass[] $reflectionClasses
     */
    public static function getCommonSuperClass(array $reflectionClasses): ?string
    {
        $parentsArrays = array_map(self::getClassParents(...), $reflectionClasses);
        $parentsIntersection = array_intersect(...$parentsArrays);

        $commonParent = reset($parentsIntersection);
        if ($commonParent !== false) {
            return $commonParent;
        }

        return null;
    }
}
