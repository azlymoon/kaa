<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test;

final class Utils
{
    public static function eval(string $code): mixed
    {
        return eval('return ' . $code . ';');
    }
}
