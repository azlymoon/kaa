<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Collection;

use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
readonly class Parameter
{
    public bool $isEnvVar;

    public string $envVarName;

    public function __construct(
        public string $name,
        public string $value,
        public ?string $binding = null,
    ) {
        // Проверяем, что value в формате %env(название_переменной)
        preg_match('/^%env\((?<var_name>.*)\)$/', $this->value, $matches);

        $this->isEnvVar = !empty($matches);
        $this->envVarName = $matches['var_name'] ?? '';
    }
}
