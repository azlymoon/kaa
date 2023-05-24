<?php

declare(strict_types=1);

namespace Kaa\Orm\Query\Builder\Expression\Condition;

use Kaa\Orm\Metadata\MetadataProviderInterface;
use Kaa\Orm\Query\Builder\Expression\ConditionTransformer;
use Kaa\Orm\Query\Builder\Expression\ExpressionInterface;
use Kaa\Orm\Query\QueryBuilder;

class Condition implements ExpressionInterface
{
    private string $condition;

    public function __construct(string $condition)
    {
        $this->condition = $condition;
    }

    public function getSql(MetadataProviderInterface $metadataProvider, QueryBuilder $queryBuilder): string
    {
        return ConditionTransformer::toSql($this->condition, $queryBuilder->getEntities(), $metadataProvider);
    }
}
