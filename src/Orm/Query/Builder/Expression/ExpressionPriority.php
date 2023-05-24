<?php

declare(strict_types=1);

namespace Kaa\Orm\Query\Builder\Expression;

class ExpressionPriority
{
    public const SELECT = 0;
    public const FROM = 10;

    public const JOIN = 20;

    public const WHERE = 30;

    public const ORDER = 40;

    public const LIMIT = 50;

    public const OFFSET = 60;
}
