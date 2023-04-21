<?php

declare(strict_types=1);

namespace Kaa\Security\Voter;

use Kaa\Security\SecurityVote;
use Kaa\Security\Token\TokenInterface;

class RoleVoter implements RoleVoterInterface
{
    public function vote(TokenInterface $token, array $requiredRoles): SecurityVote
    {
        if (array_intersect($token->getRoles(), $requiredRoles) == $requiredRoles) {
            return SecurityVote::Grant;
        }
        return SecurityVote::Deny;
    }
}
