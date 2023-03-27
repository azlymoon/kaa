<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Collection;

use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
class Dependency
{
    public function __construct(
        public string $type,
        public string $name,
        public string $injectedName = '',
    ) {
    }

    public function isParameter(): bool
    {
        return in_array($this->type, ['int', 'float', 'string', 'bool'], true);
    }

    public function isService(): bool
    {
        return class_exists($this->type);
    }

    public function isInjected(): bool
    {
        return $this->injectedName !== '';
    }
}
