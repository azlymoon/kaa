<?php

/*
 * This file has been rewritten for KPHP compilation.
 * Please refer to the original Symfony HttpFoundation repository for the original source code.
 * @see https://github.com/symfony/http-foundation
 * @author Mikhail Fedosov <fedosovmichael@gmail.com>
 *
 * This file was rewritten from the Symfony package
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kaa\HttpFoundation\KphpTests;

use Kaa\HttpFoundation\AcceptHeaderItem;

class AcceptHeaderItemTest
{
    /**
     * @dataProvider provideFromStringData
     */
    public function testFromString(): void
    {
        $data = self::provideFromStringData();
        foreach ($data as $input) {
            $string = (string)$input[0];
            $value = (string)$input[1];
            $attributes = array_map('strval', $input[2]);

            $item = AcceptHeaderItem::fromString($string);
            var_dump($value === $item->getValue());
            var_dump($attributes === $item->getAttributes());
        }
    }

    /** @return mixed[] */
    public static function provideFromStringData()
    {
        return [
            [
                'text/html',
                'text/html', [],
            ],
            [
                '"this;should,not=matter"',
                'this;should,not=matter', [],
            ],
            [
                "text/plain; charset=utf-8;param=\"this;should,not=matter\";\tfootnotes=true",
                'text/plain', ['charset' => 'utf-8', 'param' => 'this;should,not=matter', 'footnotes' => 'true'],
            ],
            [
                '"this;should,not=matter";charset=utf-8',
                'this;should,not=matter', ['charset' => 'utf-8'],
            ],
        ];
    }

    /**
     * @dataProvider provideToStringData
     */
    public function testToString(): void
    {
        $data = self::provideToStringData();
        foreach ($data as $input) {
            $value = (string)$input[0];
            $attributes = array_map('strval', $input[1]);
            $string = (string)$input[2];

            $item = new AcceptHeaderItem($value, $attributes);
            var_dump($string === (string) $item);
        }
    }

    /** @return mixed[] */
    public static function provideToStringData()
    {
        return [
            [
                'text/html', [],
                'text/html',
            ],
            [
                'text/plain', ['charset' => 'utf-8', 'param' => 'this;should,not=matter', 'footnotes' => 'true'],
                'text/plain; charset=utf-8; param="this;should,not=matter"; footnotes=true',
            ],
        ];
    }

    public function testValue(): void
    {
        $item = new AcceptHeaderItem('value', []);
        var_dump('value' === $item->getValue());

        $item->setValue('new value');
        var_dump('new value' === $item->getValue());

        $item->setValue('1');
        var_dump('1' === $item->getValue());
    }

    public function testQuality(): void
    {
        $item = new AcceptHeaderItem('value', []);
        // for float compares
        $e = 0.01;
        var_dump(abs(1.0 - $item->getQuality()) < $e);

        $item->setQuality(0.5);
        var_dump(abs(0.5 - $item->getQuality()) < $e);

        $item->setAttribute('q', '0.75');
        var_dump(abs(0.75 - $item->getQuality()) < $e);
        var_dump(false === $item->hasAttribute('q'));
    }

    public function testAttribute(): void
    {
        $item = new AcceptHeaderItem('value', []);
        var_dump([] === $item->getAttributes());
        var_dump(false === $item->hasAttribute('test'));
        var_dump(null === $item->getAttribute('test'));
        var_dump('default' === $item->getAttribute('test', 'default'));

        $item->setAttribute('test', 'value');
        var_dump(['test' => 'value'] === $item->getAttributes());
        var_dump(true === $item->hasAttribute('test'));
        var_dump('value' === $item->getAttribute('test'));
        var_dump('value' === $item->getAttribute('test', 'default'));
    }
}
