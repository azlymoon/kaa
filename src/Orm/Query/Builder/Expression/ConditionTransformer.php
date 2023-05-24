<?php

declare(strict_types=1);

namespace Kaa\Orm\Query\Builder\Expression;

use Kaa\Orm\Metadata\MetadataProviderInterface;

class ConditionTransformer
{
    /**
     * @param string[] $entities
     */
    public static function toSql(string $query, array $entities, MetadataProviderInterface $metadataProvider): string
    {
        $parts = explode(' ', $query);

        foreach ($parts as $index => $part) {
            if (!str_contains($part, '.')) {
                continue;
            }

            [$alias, $field] = explode('.', $part);
            if (!array_key_exists($alias, $entities)) {
                continue;
            }

            $metadata = $metadataProvider->getEntityMetadata($entities[$alias]);
            $sqlField = $metadata->getSqlField($field);

            $parts[$index] = $alias . '.' . $sqlField;
        }

        return implode(' ', $parts);
    }
}
