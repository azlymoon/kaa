<?php

declare(strict_types=1);

namespace Kaa\Security\Voter;

use Kaa\Security\SecurityVote;

interface RoleVoterInterface
{
    /**
     * @param string[] $token
     * @param mixed $subject
     * @param array $attributes
     * @return SecurityVote
     */
    public function vote(array $userRoles, array $requiredRoles): SecurityVote;
}