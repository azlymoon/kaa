<?php

namespace Kaa\HttpFoundation\KphpTests;

class ObjectWithToStringMethod
{
    public function __toString(): string
    {
        return '{}';
    }
}
