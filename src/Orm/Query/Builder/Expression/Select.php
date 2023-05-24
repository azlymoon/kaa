<?php

declare(strict_types=1);

namespace Kaa\Orm\Query\Builder\Expression;

use Kaa\Orm\Metadata\MetadataProviderInterface;
use Kaa\Orm\Query\QueryBuilder;

class Select implements ExpressionInterface
{
    public function getSql(MetadataProviderInterface $metadataProvider, QueryBuilder $queryBuilder): string
    {

        $selectList = [];

        foreach ($queryBuilder->getEntities() as $alias => $joinedEntityClass) {
            foreach ($metadataProvider->getEntityMetadata($joinedEntityClass)->getSqlFields() as $field) {
                $selectList[] = sprintf(', %s.%s', $alias, $field);
            }
        }

        return 'SELECT ' . implode(', ', $selectList);
    }
}
