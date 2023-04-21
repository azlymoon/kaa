<?php

declare(strict_types=1);

namespace Kaa\Security\Voter;

use Kaa\Security\SecurityVote;
use Kaa\Security\Token\TokenInterface;

interface RoleVoterInterface
{
    /**
     * @param string[] $requiredRoles
     */
    public function vote(TokenInterface $token, array $requiredRoles): SecurityVote;
}
