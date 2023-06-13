<?php

namespace Kaa\HttpKernel\Test;

use InvalidArgumentException;
use Kaa\HttpFoundation\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    public function testLessThan100HttpCodeThrows(): void
    {
        $response = new Response();

        $this->expectException(InvalidArgumentException::class);
        $response->setStatusCode(99);
    }

    public function testMoreThan600HttpCodeThrows(): void
    {
        $response = new Response();

        $this->expectException(InvalidArgumentException::class);
        $response->setStatusCode(600);
    }
}
