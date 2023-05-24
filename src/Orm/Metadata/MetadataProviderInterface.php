<?php

declare(strict_types=1);

namespace Kaa\Orm\Metadata;

interface MetadataProviderInterface
{
    public function getEntityMetadata(string $class): EntityMetadata;
}
