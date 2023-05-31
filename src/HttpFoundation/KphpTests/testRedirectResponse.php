<?php

use Kaa\HttpFoundation\KphpTests\RedirectResponseTest;

require __DIR__ . '/../../../vendor/autoload.php';

$test = new RedirectResponseTest();

echo"\ntestGenerateMetaRedirect\n";
$test->testGenerateMetaRedirect();

echo"\ntestRedirectResponseConstructorEmptyUrl\n";
$test->testRedirectResponseConstructorEmptyUrl();

echo"\ntestRedirectResponseConstructorWrongStatusCode\n";
$test->testRedirectResponseConstructorWrongStatusCode();

echo"\ntestGenerateLocationHeader\n";
$test->testGenerateLocationHeader();

echo"\ntestGetTargetUrl\n";
$test->testGetTargetUrl();

echo"\ntestSetTargetUrl\n";
$test->testSetTargetUrl();

echo"\ntestCacheHeaders\n";
$test->testCacheHeaders();
