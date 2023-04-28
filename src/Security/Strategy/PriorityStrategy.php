<?php

declare(strict_types=1);

namespace Kaa\Security\Strategy;

use Kaa\Security\SecurityVote;

class PriorityStrategy implements SecurityStrategyInterface
{
    private bool $allowIfAllAbstain;

    public function __construct(bool $allowIfAllAbstain = false)
    {
        $this->allowIfAllAbstain = $allowIfAllAbstain;
    }

    public function decide(array $results): bool
    {
        foreach ($results as $result) {
            if (SecurityVote::Grant === $result) {
                return true;
            }

            if (SecurityVote::Deny === $result) {
                return false;
            }
        }

        return $this->allowIfAllAbstain;
    }
}