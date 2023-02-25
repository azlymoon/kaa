<?php

namespace Kaa\EventDispatcher;

interface EventInterface
{
    public function stopPropagation(): void;

    public function isPropagationStopped(): bool;
}
