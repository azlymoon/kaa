<?php

declare(strict_types=1);

namespace Kaa\Orm\Query\Query;

interface QueryCreatorInterface
{
    /**
     * @param string[] $entities
     */
    public function createQuery(string $sql, array $entities): QueryInterface;
}
