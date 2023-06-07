<?php

declare(strict_types=1);

namespace Kaa\Security\Voter;

use Kaa\Security\SecurityVote;
use Kaa\Security\Token\TokenInterface;

class RoleVoter implements VoterInterface
{
    // in this case, $requiredAttributes contain required roles
    public function vote(TokenInterface $token, array $requiredAttributes): SecurityVote
    {
        if (array_intersect($token->getRoles(), $requiredAttributes) == $requiredAttributes) {
            return SecurityVote::Grant;
        }
        return SecurityVote::Deny;
    }
}
