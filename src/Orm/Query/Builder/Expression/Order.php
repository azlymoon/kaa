<?php

declare(strict_types=1);

namespace Kaa\Orm\Query\Builder\Expression;

use Kaa\Orm\Metadata\MetadataProviderInterface;
use Kaa\Orm\Query\QueryBuilder;

class Order implements ExpressionInterface
{
    public const DESC = 'DESC';

    public const ASC = 'ASC';

    public string $expr;

    public string $direction;

    public function __construct(string $expr, string $direction)
    {
        $this->expr = $expr;
        $this->direction = $direction;
    }

    public function getSql(MetadataProviderInterface $metadataProvider, QueryBuilder $queryBuilder): string
    {
        return 'ORDER BY '
            . ConditionTransformer::toSql($this->expr, $queryBuilder->getEntities(), $metadataProvider)
            . $this->direction;
    }
}
