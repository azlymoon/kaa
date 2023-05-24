<?php

declare(strict_types=1);

namespace Kaa\Orm\Query\Builder\Expression\Condition;

class OrExpr extends AbstractComplexCondition
{
    protected function getType(): string
    {
        return 'OR';
    }
}
