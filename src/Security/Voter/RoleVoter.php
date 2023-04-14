<?php

declare(strict_types=1);

namespace Kaa\Security\Voter;

use Kaa\Security\SecurityVote;

class RoleVoter implements RoleVoterInterface
{
    public function vote(array $userRoles, array $requiredRoles): SecurityVote
    {
        if (array_intersect($userRoles, $requiredRoles) == $requiredRoles) {
            return SecurityVote::Grant;
        }
        return SecurityVote::Deny;
    }
}