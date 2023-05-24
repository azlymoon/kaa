<?php

declare(strict_types=1);

namespace Kaa\Orm\Query\Builder\Expression\Condition;

class AndExpr extends AbstractComplexCondition
{
    protected function getType(): string
    {
        return 'AND';
    }
}
