<?php

declare(strict_types=1);

namespace Kaa\Router\ActionFinder;

use Exception;
use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\Router\Action;

/**
 * Возвращает Http-действия, объявленные пользователем
 */
#[PhpOnly]
interface ActionFinderInterface
{
    /**
     * @param mixed[] $userConfig
     * @return Action[]
     * @throws Exception
     */
    public function find(array $userConfig): array;
}
