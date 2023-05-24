<?php

declare(strict_types=1);

namespace Kaa\Orm\Query\Builder;

use Kaa\Orm\Exception\QueryBuilderException;
use Kaa\Orm\Metadata\MetadataProviderInterface;
use Kaa\Orm\Query\Builder\Expression\ExpressionInterface;
use Kaa\Orm\Query\Builder\Expression\ExpressionPriority;
use Kaa\Orm\Query\Builder\Expression\From;
use Kaa\Orm\Query\Builder\Expression\Join;
use Kaa\Orm\Query\Builder\Expression\Limit;
use Kaa\Orm\Query\Builder\Expression\Offset;
use Kaa\Orm\Query\Builder\Expression\Order;
use Kaa\Orm\Query\Builder\Expression\Select;
use Kaa\Orm\Query\Builder\Expression\Where;
use Kaa\Orm\Query\Query\QueryCreatorInterface;
use Kaa\Orm\Query\Query\QueryInterface;

class QueryBuilder
{
    /** @readonly  */
    private QueryCreatorInterface $queryCreator;

    /** @readonly */
    private MetadataProviderInterface $metadataProvider;

    /** @var ExpressionInterface[][] */
    private array $parts = [];

    /** @var string[] */
    private array $entities;

    /** @var string[] */
    private array $joins = [];

    public function __construct(
        QueryCreatorInterface $queryCreator,
        MetadataProviderInterface $metadataProvider,
        string $fromEntity,
        string $alias,
    ) {
        $this->queryCreator = $queryCreator;
        $this->metadataProvider = $metadataProvider;

        $this->parts[ExpressionPriority::SELECT] = [new Select()];
        $this->parts[ExpressionPriority::FROM] = new From($fromEntity, $alias);

        $this->entities = [$alias => $fromEntity];
    }

    public function join(string $join, string $alias): self
    {
        return $this->anyJoin($join, $alias, Join::INNER);
    }

    public function leftJoin(string $join, string $alias): self
    {
        return $this->anyJoin($join, $alias, Join::LEFT);
    }


    private function anyJoin(string $join, string $alias, string $type): self
    {
        [$baseAlias, $field] = explode('.', $join);
        $entity = array_search($baseAlias, $this->entities, true);

        $metadata = $this->metadataProvider->getEntityMetadata($entity);
        $joinMetadata = $metadata->getJoinMetadataBy($field);

        $this->entities[$baseAlias] = $joinMetadata;

        if (!array_key_exists(ExpressionPriority::JOIN, $this->parts)) {
            $this->parts[ExpressionPriority::JOIN] = [];
        }

        $this->parts[ExpressionPriority::JOIN][] = new Join(
            $type,
            $joinMetadata,
            $baseAlias,
            $alias
        );

        $this->joins[] = $joinMetadata->getUniqueName();

        return $this;
    }

    /**
     * @throws QueryBuilderException
     */
    public function where(ExpressionInterface $expression): self
    {
        if (array_key_exists(ExpressionPriority::WHERE, $this->parts)) {
            throw new QueryBuilderException('Where is already set');
        }

        $this->parts[ExpressionPriority::WHERE] = [new Where($expression)];

        return $this;
    }

    /**
     * @throws QueryBuilderException
     */
    public function limit(int $limit): self
    {
        if (array_key_exists(ExpressionPriority::LIMIT, $this->parts)) {
            throw new QueryBuilderException('Limit is already set');
        }

        $this->parts[ExpressionPriority::LIMIT][] = new Limit($limit);

        return $this;
    }

    /**
     * @throws QueryBuilderException
     */
    public function offset(int $offset): self
    {
        if (array_key_exists(ExpressionPriority::OFFSET, $this->parts)) {
            throw new QueryBuilderException('Offset is already set');
        }

        $this->parts[ExpressionPriority::OFFSET][] = new Offset($offset);

        return $this;
    }

    public function orderBy(string $expr, string $direction): self
    {
        if (!array_key_exists(ExpressionPriority::ORDER, $this->parts)) {
            $this->parts[ExpressionPriority::ORDER] = [];
        }

        $this->parts[ExpressionPriority::ORDER][] = new Order($expr, $direction);

        return $this;
    }

    /**
     * @return string[]
     */
    public function getEntities(): array
    {
        return $this->entities;
    }

    public function getQuery(): QueryInterface
    {
        $this->addJoins();

        $sql = '';

        foreach ($this->parts as $parts) {
            foreach ($parts as $part) {
                $sql .= ' ' . $part->getSql($this->metadataProvider, $this);
            }
        }

        return $this->queryCreator->createQuery($sql, $this->entities);
    }

    private function addJoins(): void
    {
        $identifierNum = 1;

        for (
            $entity = reset($this->entities);
            ($alias = key($this->entities)) !== null;
            $entity = next($this->entities)
        ) {
            foreach ($this->metadataProvider->getEntityMetadata($entity)->getAllJoins() as $joinMetadata) {
                if (in_array($joinMetadata->getUniqueName(), $this->joins, true)) {
                    continue;
                }

                $targetAlias = 'ihunique' . $identifierNum++;
                $this->parts[ExpressionPriority::JOIN][] = new Join(
                    Join::LEFT,
                    $joinMetadata,
                    $alias,
                    $targetAlias,
                );

                $this->joins[] = $joinMetadata->getUniqueName();
                $this->entities[$targetAlias] = $joinMetadata->getTargetEntity();
            }
        }
    }
}
