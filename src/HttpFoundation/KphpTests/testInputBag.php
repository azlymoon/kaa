<?php

use Kaa\HttpFoundation\KphpTests\InputBagTest;

require __DIR__ . '/../../../vendor/autoload.php';

$test = new InputBagTest();

echo"\ntestGet\n";
$test->testGet();

echo"\ntestGetDoesNotUseDeepByDefault\n";
$test->testGetDoesNotUseDeepByDefault();

echo"\ntestGettingANonStringValue\n";
$test->testGettingANonStringValue();

echo"\ntestGetWithNonStringDefaultValue\n";
$test->testGetWithNonStringDefaultValue();
