<?php

declare(strict_types=1);

namespace Kaa\Orm\Query\Builder\Expression;

use Kaa\Orm\Metadata\MetadataProviderInterface;
use Kaa\Orm\Query\QueryBuilder;

class Limit implements ExpressionInterface
{
    public int $limit;

    public function __construct(int $limit)
    {
        $this->limit = $limit;
    }

    public function getSql(MetadataProviderInterface $metadataProvider, QueryBuilder $queryBuilder): string
    {
        return 'LIMIT ' . $this->limit;
    }
}
