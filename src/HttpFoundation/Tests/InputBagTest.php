<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kaa\HttpFoundation\Tests;

use PHPUnit\Framework\TestCase;
use Kaa\HttpFoundation\Exception\BadRequestException;
use Kaa\HttpFoundation\InputBag;

class InputBagTest extends TestCase
{
    public function testGet()
    {
        $bag = new InputBag([
            'foo' => 'bar',
            'null' => null,
            'int' => 1,
            'float' => 1.0,
            'bool' => false
        ]);

        $this->assertSame('bar', $bag->get('foo'), '->get() gets the value of a string parameter');
        $this->assertSame('default', $bag->get('unknown', 'default'), '->get() returns second argument as default if a parameter is not defined');
        $this->assertNull($bag->get('null', 'default'), '->get() returns null if null is set');
        $this->assertSame(1, $bag->get('int'), '->get() gets the value of an int parameter');
        $this->assertSame(1.0, $bag->get('float'), '->get() gets the value of a float parameter');
        $this->assertFalse($bag->get('bool'), '->get() gets the value of a bool parameter');
    }

    public function testGetDoesNotUseDeepByDefault()
    {
        $bag = new InputBag(['foo' => ['bar' => 'moo']]);

        $this->assertNull($bag->get('foo[bar]'));
    }

    public function testSetWithNonScalarOrArray()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a scalar, or an array as a 2nd argument to "Kaa\HttpFoundation\InputBag::set()"');

        $bag = new InputBag();
        $bag->set('foo', new InputBag());
    }

    public function testGettingANonStringValue()
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Input value "foo" contains a non-scalar value.');

        $bag = new InputBag(['foo' => ['a', 'b']]);
        $bag->get('foo');
    }

    public function testGetWithNonStringDefaultValue()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a scalar value as a 2nd argument to "Kaa\HttpFoundation\InputBag::get()');

        $bag = new InputBag(['foo' => 'bar']);
        $bag->get('foo', ['a', 'b']);
    }
}
