<?php

declare(strict_types=1);

namespace Kaa\Orm\Query\Builder\Expression;

use Kaa\Orm\Metadata\JoinMetadata;
use Kaa\Orm\Metadata\MetadataProviderInterface;
use Kaa\Orm\Query\QueryBuilder;

class Join implements ExpressionInterface
{
    public const INNER = 'INNER';

    public const LEFT = 'LEFT';

    private string $joinType;

    private JoinMetadata $joinMetadata;

    private string $baseAlias;

    private string $targetAlias;

    public function __construct(
        string $joinType,
        JoinMetadata $joinMetadata,
        string $baseAlias,
        string $targetAlias
    ) {
        $this->joinType = $joinType;
        $this->joinMetadata = $joinMetadata;
        $this->baseAlias = $baseAlias;
        $this->targetAlias = $targetAlias;
    }


    public function getSql(MetadataProviderInterface $metadataProvider, QueryBuilder $queryBuilder): string
    {
        $sql = '%joinType% JOIN %targetTable% %targetAlias% ON %baseAlias%.%baseColumn% = %targetAlias%.%targetColumn%';

        $replacements = [
            '%joinType%' => $this->joinType,
            '%targetTable%' => $this->joinMetadata->getTargetTable(),
            '%targetAlias%' => $this->targetAlias,
            '%baseAlias%' => $this->baseAlias,
            '%baseColumn%' => $this->joinMetadata->getBaseColumn(),
            '%targetColumn%' => $this->joinMetadata->getTargetColumn()
        ];

        return strtr($sql, $replacements);
    }
}
