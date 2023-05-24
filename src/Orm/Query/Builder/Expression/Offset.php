<?php

declare(strict_types=1);

namespace Kaa\Orm\Query\Builder\Expression;

use Kaa\Orm\Metadata\MetadataProviderInterface;
use Kaa\Orm\Query\QueryBuilder;

class Offset implements ExpressionInterface
{
    public int $offset;

    public function __construct(int $offset)
    {
        $this->offset = $offset;
    }

    public function getSql(MetadataProviderInterface $metadataProvider, QueryBuilder $queryBuilder): string
    {
        return 'OFFSET ' . $this->offset;
    }
}
