<?php

namespace Kaa\Validator\Assert;

use Attribute;
use Kaa\CodeGen\Attribute\PhpOnly;

#[Attribute(Attribute::TARGET_PROPERTY)]
#[PhpOnly]
readonly class Type extends Assert
{

    /**
     * @param string|string[] $type
     * @param string|null $message
     * @param string[] $allowTypes
     */
    public function __construct(
        public string|array $type,
        public ?string $message = null,
        protected array $allowTypes = [
            'bool',
            'boolean',
            'int',
            'integer',
            'long',
            'float',
            'double',
            'real',
            'numeric',
            'string',
            'scalar',
            'array',
            'iterable',
            'countable',
            'callable',
            'object',
            'null',
            'alnum',
            'alpha',
            'cntrl',
            'digit',
            'graph',
            'lower',
            'print',
            'punct',
            'space',
            'upper',
            'xdigit',
        ],
    ) {
    }

    public function supportsType(string $typeName): bool
    {
        return (in_array($typeName, $this->allowTypes, true));
    }

    /**
     * @return string[]
     */
    public function getAllowTypes(): array
    {
        return $this->allowTypes;
    }
}
