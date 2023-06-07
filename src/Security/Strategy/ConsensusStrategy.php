<?php

declare(strict_types=1);

namespace Kaa\Security\Strategy;

use Kaa\Security\SecurityVote;

class ConsensusStrategy implements SecurityStrategyInterface
{
    private bool $allowIfAllAbstain;

    private bool $allowIfEqualGrantDeny;

    public function __construct(bool $allowIfAllAbstain = false, bool $allowIfEqualGrantDeny = true)
    {
        $this->allowIfAllAbstain = $allowIfAllAbstain;
        $this->allowIfEqualGrantDeny = $allowIfEqualGrantDeny;
    }

    public function decide(array $results): bool
    {
        /**
         * @param int $grant
         */
        $grant = 0;
        /**
         * @param int $deny
         */
        $deny = 0;
        foreach ($results as $result) {
            if (SecurityVote::Grant === $result) {
                ++$grant;
            } elseif (SecurityVote::Deny === $result) {
                ++$deny;
            }
        }

        if ($grant > $deny) {
            return true;
        }

        if ($deny > $grant) {
            return false;
        }

        if ($grant > 0) {
            return $this->allowIfEqualGrantDeny;
        }

        return $this->allowIfAllAbstain;
    }
}
