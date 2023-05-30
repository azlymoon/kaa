<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpFoundation\Tests;

use PHPUnit\Framework\TestCase;
use Kaa\HttpFoundation\Exception\BadRequestException;
use Kaa\HttpFoundation\ParameterBag;

class ParameterBagTest extends TestCase
{
    public function testConstructor()
    {
        $this->testAll();
    }

    public function testAll()
    {
        $bag = new ParameterBag(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $bag->all(), '->all() gets all the input');
    }

    public function testAllWithInputKey()
    {
        $bag = new ParameterBag(['foo' => ['bar', 'baz'], 'null' => null]);

        $this->assertEquals(['bar', 'baz'], $bag->all('foo'), '->all() gets the value of a parameter');
        $this->assertEquals([], $bag->all('unknown'), '->all() returns an empty array if a parameter is not defined');
    }

    public function testAllThrowsForNonArrayValues()
    {
        $this->expectException(BadRequestException::class);
        $this->markTestSkipped('This test is not required for KPHP because of its strict typing');
        $bag = new ParameterBag(['foo' => 'bar', 'null' => null]);
        $bag->all('foo');
    }

    public function testKeys()
    {
        $bag = new ParameterBag(['foo' => 'bar']);
        $this->assertEquals(['foo'], $bag->keys());
    }

    public function testAdd()
    {
        $bag = new ParameterBag(['foo' => 'bar']);
        $bag->add(['bar' => 'bas']);
        $this->assertEquals(['foo' => 'bar', 'bar' => 'bas'], $bag->all());
    }

    public function testRemove()
    {
        $bag = new ParameterBag(['foo' => 'bar']);
        $bag->add(['bar' => 'bas']);
        $this->assertEquals(['foo' => 'bar', 'bar' => 'bas'], $bag->all());
        $bag->remove('bar');
        $this->assertEquals(['foo' => 'bar'], $bag->all());
    }

    public function testReplace()
    {
        $bag = new ParameterBag(['foo' => 'bar']);

        $bag->replace(['FOO' => 'BAR']);
        $this->assertEquals(['FOO' => 'BAR'], $bag->all(), '->replace() replaces the input with the argument');
        $this->assertFalse($bag->has('foo'), '->replace() overrides previously set the input');
    }

    public function testGet()
    {
        $bag = new ParameterBag(['foo' => 'bar', 'null' => null]);

        $this->assertEquals('bar', $bag->get('foo'), '->get() gets the value of a parameter');
        $this->assertEquals('default', $bag->get('unknown', 'default'), '->get() returns second argument as default if a parameter is not defined');
        $this->assertNull($bag->get('null', 'default'), '->get() returns null if null is set');
    }

    public function testGetDoesNotUseDeepByDefault()
    {
        $bag = new ParameterBag(['foo' => ['bar' => 'moo']]);

        $this->assertNull($bag->get('foo[bar]'));
    }

    public function testSet()
    {
        $bag = new ParameterBag([]);

        $bag->set('foo', 'bar');
        $this->assertEquals('bar', $bag->get('foo'), '->set() sets the value of parameter');

        $bag->set('foo', 'baz');
        $this->assertEquals('baz', $bag->get('foo'), '->set() overrides previously set parameter');
    }

    public function testHas()
    {
        $bag = new ParameterBag(['foo' => 'bar']);

        $this->assertTrue($bag->has('foo'), '->has() returns true if a parameter is defined');
        $this->assertFalse($bag->has('unknown'), '->has() return false if a parameter is not defined');
    }

    public function testGetAlpha()
    {
        $bag = new ParameterBag(['word' => 'foo_BAR_012']);

        $this->assertEquals('fooBAR', $bag->getAlpha('word'), '->getAlpha() gets only alphabetic characters');
        $this->assertEquals('', $bag->getAlpha('unknown'), '->getAlpha() returns empty string if a parameter is not defined');
    }

    public function testGetAlnum()
    {
        $bag = new ParameterBag(['word' => 'foo_BAR_012']);

        $this->assertEquals('fooBAR012', $bag->getAlnum('word'), '->getAlnum() gets only alphanumeric characters');
        $this->assertEquals('', $bag->getAlnum('unknown'), '->getAlnum() returns empty string if a parameter is not defined');
    }

    public function testGetDigits()
    {
        $bag = new ParameterBag(['word' => 'foo_BAR_012']);

        $this->assertEquals('012', $bag->getDigits('word'), '->getDigits() gets only digits as string');
        $this->assertEquals('', $bag->getDigits('unknown'), '->getDigits() returns empty string if a parameter is not defined');
    }

    public function testGetInt()
    {
        $bag = new ParameterBag(['digits' => '0123']);

        $this->assertEquals(123, $bag->getInt('digits'), '->getInt() gets a value of parameter as integer');
        $this->assertEquals(0, $bag->getInt('unknown'), '->getInt() returns zero if a parameter is not defined');
    }

    public function testGetIterator()
    {
        $parameters = ['foo' => 'bar', 'hello' => 'world'];
        $bag = new ParameterBag($parameters);

        $i = 0;
        foreach ($bag->all() as $key => $val) {
            ++$i;
            $this->assertEquals($parameters[$key], $val);
        }

        $this->assertEquals(\count($parameters), $i);
    }

    public function testCount()
    {
        $parameters = ['foo' => 'bar', 'hello' => 'world'];
        $bag = new ParameterBag($parameters);

        $this->assertCount(\count($parameters), $bag->all());
    }

    public function testGetBoolean()
    {
        $parameters = ['string_true' => 'true', 'string_false' => 'false'];
        $bag = new ParameterBag($parameters);

        $this->assertTrue($bag->getBoolean('string_true'), '->getBoolean() gets the string true as boolean true');
        $this->assertFalse($bag->getBoolean('string_false'), '->getBoolean() gets the string false as boolean false');
        $this->assertFalse($bag->getBoolean('unknown'), '->getBoolean() returns false if a parameter is not defined');
    }
}
