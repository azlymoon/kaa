<?php

namespace Kaa\HttpFoundation\KphpTests;

class UserObjectJsonResponse
{
    public string $name;

    public int $age;

    public function __construct(string $name, int $age)
    {
        $this->name = $name;
        $this->age = $age;
    }
}
