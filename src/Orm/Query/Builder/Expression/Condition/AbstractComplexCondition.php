<?php

declare(strict_types=1);

namespace Kaa\Orm\Query\Builder\Expression\Condition;

use Kaa\Orm\Metadata\MetadataProviderInterface;
use Kaa\Orm\Query\Builder\Expression\ConditionTransformer;
use Kaa\Orm\Query\Builder\Expression\ExpressionInterface;
use Kaa\Orm\Query\QueryBuilder;

abstract class AbstractComplexCondition implements ExpressionInterface
{
    /** @var string[] */
    private array $terms = [];

    /** @var ExpressionInterface[] */
    private array $conditions = [];

    public function term(string $term): self
    {
        $this->terms[] = $term;

        return $this;
    }

    public function t(string $term): self
    {
        return $this->term($term);
    }

    public function condition(ExpressionInterface $condition): self
    {
        $this->conditions[] = $condition;

        return $this;
    }

    public function c(ExpressionInterface $condition): self
    {
        return $this->condition($condition);
    }

    public function getSql(MetadataProviderInterface $metadataProvider, QueryBuilder $queryBuilder): string
    {
        $parts = [];

        foreach ($this->terms as $term) {
            $parts[] = ConditionTransformer::toSql($term, $queryBuilder->getEntities(), $metadataProvider);
        }

        foreach ($this->conditions as $condition) {
            $parts[] = $condition->getSql($metadataProvider, $queryBuilder);
        }

        return '(' . implode(' ' . $this->getType() . ' ', $parts) . ')';
    }

    abstract protected function getType(): string;
}
