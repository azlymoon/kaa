<?php

use Kaa\HttpFoundation\KphpTests\HeaderUtilsTest;

require __DIR__ . '/../../../vendor/autoload.php';

$test = new HeaderUtilsTest();

echo"\ntestSplit\n";
$test->testSplit();

echo"\ntestCombine\n";
$test->testCombine();

echo"\ntestToString\n";
$test->testToString();

echo"\ntestQuote\n";
$test->testQuote();

echo"\ntestUnquote\n";
$test->testUnquote();

echo"\ntestMakeDispositionInvalidDisposition\n";
$test->testMakeDispositionInvalidDisposition();

echo"\ntestMakeDisposition\n";
$test->testMakeDisposition();

echo"\ntestMakeDispositionFail\n";
$test->testMakeDispositionFail();

echo"\ntestParseQuery\n";
$test->testParseQuery();

echo"\ntestParseCookie\n";
$test->testParseCookie();

echo"\ntestParseQueryIgnoreBrackets\n";
$test->testParseQueryIgnoreBrackets();
