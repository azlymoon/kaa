<?php

namespace Kaa\HttpFoundation\KphpTests;

class StringableObject
{
    public function __toString(): string
    {
        return 'Foo';
    }
}
