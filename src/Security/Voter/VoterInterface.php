<?php

declare(strict_types=1);

namespace Kaa\Security\Voter;

use Kaa\Security\SecurityVote;
use Kaa\Security\Token\TokenInterface;

interface VoterInterface
{
    /**
     * @param string[] $requiredAttributes
     */
    public function vote(TokenInterface $token, array $requiredAttributes): SecurityVote;
}
