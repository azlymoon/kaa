<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Collection\Parameter;

use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
readonly class Parameter
{
    public bool $isEnvVar;

    public string $envVarName;

    public function __construct(
        public string $name,
        public string|int|float $value,
        public ?string $binding = null,
    ) {
        if (is_string($this->value)) {
            // Проверяем, что value в формате %env(название_переменной)%
            preg_match('/^ %env \( (?<var_name>.*) \) % $/x', $this->value, $matches);

            $this->isEnvVar = !empty($matches);
            $this->envVarName = $matches['var_name'] ?? '';
        } else {
            $this->isEnvVar = false;
            $this->envVarName = '';
        }
    }
}
