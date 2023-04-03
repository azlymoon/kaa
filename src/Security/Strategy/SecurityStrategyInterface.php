<?php

declare(strict_types=1);

namespace Kaa\Security\Strategy;

use Kaa\Security\SecurityVote;

interface SecurityStrategyInterface
{
    /**
     * @param SecurityVote[] $results
     * @return bool
     */
    public function decide(array $results): bool;
}
