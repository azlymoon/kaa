<?php

declare(strict_types=1);

namespace Kaa\Security\Strategy;

use Kaa\Security\SecurityVote;

class AffirmativeStrategy implements SecurityStrategyInterface
{
    private bool $allowIfAllAbstain;

    public function __construct(bool $allowIfAllAbstain = false)
    {
        $this->allowIfAllAbstain = $allowIfAllAbstain;
    }

    public function decide(array $results): bool
    {
        /**
         * @param int $deny
         */
        $deny = 0;
        foreach ($results as $result) {
            if (SecurityVote::Grant === $result) {
                return true;
            }

            if (SecurityVote::Deny === $result) {
                ++$deny;
            }
        }

        if ($deny > 0) {
            return false;
        }

        return $this->allowIfAllAbstain;
    }
}
