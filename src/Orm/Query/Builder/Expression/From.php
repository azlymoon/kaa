<?php

declare(strict_types=1);

namespace Kaa\Orm\Query\Builder\Expression;

use Kaa\Orm\Metadata\MetadataProviderInterface;
use Kaa\Orm\Query\QueryBuilder;

class From implements ExpressionInterface
{
    private string $fromEntity;

    private string $alias;

    public function __construct(string $fromEntity, string $alias)
    {
        $this->fromEntity = $fromEntity;
        $this->alias = $alias;
    }

    public function getSql(MetadataProviderInterface $metadataProvider, QueryBuilder $queryBuilder): string
    {
        $metadata = $metadataProvider->getEntityMetadata($this->fromEntity);

        return sprintf('FROM %s %s', $metadata->getTableName(), $this->alias);
    }
}
