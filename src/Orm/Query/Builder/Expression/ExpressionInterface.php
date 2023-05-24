<?php

declare(strict_types=1);

namespace Kaa\Orm\Query\Builder\Expression;

use Kaa\Orm\Metadata\MetadataProviderInterface;
use Kaa\Orm\Query\QueryBuilder;

interface ExpressionInterface
{
    public function getSql(MetadataProviderInterface $metadataProvider, QueryBuilder $queryBuilder): string;
}
