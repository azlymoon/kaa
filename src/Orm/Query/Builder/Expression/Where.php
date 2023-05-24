<?php

declare(strict_types=1);

namespace Kaa\Orm\Query\Builder\Expression;

use Kaa\Orm\Metadata\MetadataProviderInterface;
use Kaa\Orm\Query\QueryBuilder;

class Where implements ExpressionInterface
{
    private ExpressionInterface $expression;

    public function __construct(
        ExpressionInterface $expression
    ) {
        $this->expression = $expression;
    }

    public function getSql(MetadataProviderInterface $metadataProvider, QueryBuilder $queryBuilder): string
    {
        return 'WHERE ' . $this->expression->getSql($metadataProvider, $queryBuilder);
    }
}
