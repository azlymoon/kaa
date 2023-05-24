<?php

declare(strict_types=1);

namespace Kaa\Orm\Metadata;

class EntityMetadata
{

    public function getSqlField(string $phpField): string
    {

    }

    public function getTableName(): string
    {

    }

    public function getJoinMetadataBy(string $fieldName): JoinMetadata
    {

    }

    /**
     * @return string[]
     */
    public function getSqlFields(): array
    {

    }

    /**
     * @return JoinMetadata[]
     */
    public function getAllJoins(): array
    {
    }
}
