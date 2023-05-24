<?php

declare(strict_types=1);

namespace Kaa\Orm;

interface EntityManagerInterface
{
    /**
     * @return mixed
     */
    public function execute(string $sql): array;
}
