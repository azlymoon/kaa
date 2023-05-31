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

use Kaa\HttpFoundation\AcceptHeader;
use Kaa\HttpFoundation\AcceptHeaderItem;

class AcceptHeaderTest
{
    public function testFirst()
    {
        $header = AcceptHeader::fromString('text/plain; q=0.5, text/html, text/x-dvi; q=0.8, text/x-c');
        var_dump('text/html' === $header->first()->getValue());
    }

    /**
     * @dataProvider provideFromStringData
     */
    public function testFromString(): void
    {
        $data = self::provideFromStringData();
        foreach ($data as $input) {
            $string = (string)$input[0];
            $items = array_map('strval', $input[1]);

            $header = AcceptHeader::fromString($string);
            $parsed = array_values($header->all());
            // reset index since the fixtures don't have them set
            foreach ($parsed as $item) {
                $item->setIndex(0);
            }

            /** @var string[] $ans */
            $ans = [];
            foreach ($parsed as $item) {
                $ans[] =$item->getValue();
            }
            var_dump($ans === $items);
        }
    }

    /** @return mixed */
    public static function provideFromStringData()
    {
        return [
            ['', []],
            ['gzip', ['gzip']],
            ['gzip,deflate,sdch', ['gzip','deflate','sdch']],
            ["gzip, deflate\t,sdch", ['gzip','deflate','sdch']],
            ['"this;should,not=matter"', ['this;should,not=matter']],
        ];
    }

    /**
     * @dataProvider provideToStringData
     */
    public function testToString(): void
    {
        for ($i = 0; $i < 4; $i++) {
            $items = self::provideToStringDataArrayHeaderItems()[$i];
            $string = self::provideToStringData()[$i][0];
            $header = new AcceptHeader($items);
            var_dump($string === (string)$header);
        }
    }

    /** @return string[][] */
    public static function provideToStringData()
    {
        return [
            [''],
            ['gzip'],
            ['gzip,deflate,sdch'],
            ['this;should,not=matter'],
        ];
    }

    /** @return AcceptHeaderItem[][] */
    public static function provideToStringDataArrayHeaderItems()
    {
        return [
            [],
            [new AcceptHeaderItem('gzip')],
            [new AcceptHeaderItem('gzip'), new AcceptHeaderItem('deflate'), new AcceptHeaderItem('sdch')],
            [new AcceptHeaderItem('this;should,not=matter')],
        ];
    }

    /**
     * @dataProvider provideSortingData
     */
    public function testSorting()
    {
        $data = self::provideSortingData();
        foreach ($data as $input) {
            $string = (string) $input[0];
            $values = array_map('strval', $input[1]);

            $header = AcceptHeader::fromString($string);
            var_dump($values === array_keys($header->all()));
        }
    }

    /** @return mixed */
    public static function provideSortingData()
    {
        return [
            ['*;q=0.3,ISO-8859-1,utf-8;q=0.7', ['ISO-8859-1', 'utf-8', '*']],
            ['*;q=0.3,ISO-8859-1;q=0.7,utf-8;q=0.7', ['ISO-8859-1', 'utf-8', '*']],
            ['*;q=0.3,utf-8;q=0.7,ISO-8859-1;q=0.7', ['utf-8', 'ISO-8859-1', '*']],
        ];
    }

    /**
     * @dataProvider provideDefaultValueData
     */
    public function testDefaultValue()
    {
        $data = self::provideDefaultValueData();
        foreach ($data as $input) {
            $acceptHeader = $input[0];
            $value = $input[1];
            $expectedQuality = (float)$input[2];

            $header = AcceptHeader::fromString($acceptHeader);
            var_dump(abs($expectedQuality - $header->get($value)->getQuality()) < 0.001);
        }
    }

    /** @return string[][] */
    public static function provideDefaultValueData()
    {
        return [
            ['text/plain;q=0.5, text/html, text/x-dvi;q=0.8, *;q=0.3', 'text/xml', '0.3'],
            ['text/plain;q=0.5, text/html, text/x-dvi;q=0.8, */*;q=0.3', 'text/xml', '0.3'],
            ['text/plain;q=0.5, text/html, text/x-dvi;q=0.8, */*;q=0.3', 'text/html', '1.0'],
            ['text/plain;q=0.5, text/html, text/x-dvi;q=0.8, */*;q=0.3', 'text/plain', '0.5'],
            ['text/plain;q=0.5, text/html, text/x-dvi;q=0.8, */*;q=0.3', '*', '0.3'],
            ['text/plain;q=0.5, text/html, text/x-dvi;q=0.8, */*', '*', '1.0'],
            ['text/plain;q=0.5, text/html, text/x-dvi;q=0.8, */*', 'text/xml', '1.0'],
            ['text/plain;q=0.5, text/html, text/x-dvi;q=0.8, */*', 'text/*', '1.0'],
            ['text/plain;q=0.5, text/html, text/*;q=0.8, */*', 'text/*', '0.8'],
            ['text/plain;q=0.5, text/html, text/*;q=0.8, */*', 'text/html', '1.0'],
            ['text/plain;q=0.5, text/html, text/*;q=0.8, */*', 'text/x-dvi', '0.8'],
            ['*;q=0.3, ISO-8859-1;q=0.7, utf-8;q=0.7', '*', '0.3'],
            ['*;q=0.3, ISO-8859-1;q=0.7, utf-8;q=0.7', 'utf-8', '0.7'],
            ['*;q=0.3, ISO-8859-1;q=0.7, utf-8;q=0.7', 'SHIFT_JIS', '0.3']
        ];
    }
}
