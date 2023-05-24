<?php

declare(strict_types=1);

namespace Kaa\Orm\Query\Query;

interface QueryInterface
{
    /**
     * @return mixed
     */
    public function getArrayResult(): array;

    /**
     * @kphp-generic T
     * @param class-string<T> $entityClass
     * @return T|null
     */
    public function getOneOrNull(string $entityClass): ?object;

    /**
     * @kphp-generic T
     * @param class-string<T> $entityClass
     * @return T[]
     */
    public function getResult(string $entityClass): array;
}
