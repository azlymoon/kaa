<?php

declare(strict_types=1);

namespace Kaa\Orm\Query\Query;

use Kaa\Orm\EntityManagerInterface;

abstract class AbstractQuery implements QueryInterface
{
    private EntityManagerInterface $entityManager;
    private string $sql;

    /** @var string[]  */
    private array $entities;

    /**
     * @param string[] $entities
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        string $sql,
        array $entities,
    ) {
        $this->entities = $entities;
        $this->sql = $sql;
        $this->entityManager = $entityManager;
    }

    public function getArrayResult(): array
    {
        return $this->entityManager->execute($this->sql);
    }

    public function getOneOrNull(string $entityClass): ?object
    {
        return $this->getResult($entityClass)[0] ?? null;
    }
}
