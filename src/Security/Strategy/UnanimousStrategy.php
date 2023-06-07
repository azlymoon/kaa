<?php

declare(strict_types=1);

namespace Kaa\Security\Strategy;

use Kaa\Security\SecurityVote;

class UnanimousStrategy implements SecurityStrategyInterface
{
    private bool $allowIfAllAbstain;

    public function __construct(bool $allowIfAllAbstain = false)
    {
        $this->allowIfAllAbstain = $allowIfAllAbstain;
    }

    public function decide(array $results): bool
    {
        /**
        * @param int $grant
        */
        $grant = 0;
        foreach ($results as $result) {
            if (SecurityVote::Deny === $result) {
                return false;
            }

            if (SecurityVote::Grant === $result) {
                ++$grant;
            }
        }

        // no deny votes
        if ($grant > 0) {
            return true;
        }

        return $this->allowIfAllAbstain;
    }
}
