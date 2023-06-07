<?php

declare(strict_types=1);

namespace Kaa\Security;

enum SecurityVote
{
    case Deny;
    case Abstain;
    case Grant;
}
