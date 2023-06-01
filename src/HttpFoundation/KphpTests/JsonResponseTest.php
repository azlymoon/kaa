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

use Kaa\HttpFoundation\JsonResponse;

class JsonResponseTest
{
    public function testConstructorEmptyCreatesJsonObject(): void
    {
        $response = new JsonResponse();
        var_dump('{}' === $response->getContent());
    }

    public function testConstructorWithArrayCreatesJsonArray(): void
    {
        $response = new JsonResponse([0, 1, 2, 3]);
        var_dump('[0,1,2,3]' === $response->getContent());
    }

    public function testConstructorWithAssocArrayCreatesJsonObject(): void
    {
        $response = new JsonResponse(['foo' => 'bar']);
        var_dump('{"foo":"bar"}' === $response->getContent());
    }

    public function testConstructorWithSimpleTypes(): void
    {
        $response = new JsonResponse('foo');
        var_dump('"foo"' === $response->getContent());

        $response = new JsonResponse(0);
        var_dump('0' === $response->getContent());

        $response = new JsonResponse(0.1);
        var_dump('0.1' === $response->getContent());

        $response = new JsonResponse(true);
        var_dump('true' === $response->getContent());
    }

    public function testConstructorWithCustomStatus(): void
    {
        $response = new JsonResponse([], 202);
        var_dump(202 === $response->getStatusCode());
    }

    public function testConstructorAddsContentTypeHeader(): void
    {
        $response = new JsonResponse();
        var_dump('application/json' === $response->headers->get('Content-Type'));
    }

    public function testConstructorWithCustomHeaders(): void
    {
        $response = new JsonResponse([], 200, ['ETag' => 'foo']);
        var_dump('application/json' === $response->headers->get('Content-Type'));
        var_dump('foo' === $response->headers->get('ETag'));
    }

    public function testConstructorWithCustomContentType(): void
    {
        $headers = ['Content-Type' => 'application/vnd.acme.blog-v1+json'];

        $response = new JsonResponse([], 200, $headers);
        var_dump('application/vnd.acme.blog-v1+json' === $response->headers->get('Content-Type'));
    }

    public function testSetJson(): void
    {
        $response = new JsonResponse('1', 200, [], true);
        var_dump('1' === $response->getContent());

        $response = new JsonResponse('[1]', 200, [], true);
        var_dump('[1]' === $response->getContent());

        $response = new JsonResponse(null, 200, []);
        $response->setJson('true');
        var_dump('true' === $response->getContent());
    }

    public function testSetCallback(): void
    {
        $response = (new JsonResponse(['foo' => 'bar']))->setCallback('callback');

        var_dump('/**/callback({"foo":"bar"});' === $response->getContent());
        var_dump('text/javascript' === $response->headers->get('Content-Type'));
    }

    public function testJsonEncodeFlags(): void
    {
        $response = new JsonResponse('<>\'&"');

        var_dump('"\u003C\u003E\u0027\u0026\u0022"' === $response->getContent());
    }

    public function testGetEncodingOptions(): void
    {
        $response = new JsonResponse();

        var_dump($response->getEncodingOptions() === ['JSON_HEX_TAG', 'JSON_HEX_APOS', 'JSON_HEX_AMP', 'JSON_HEX_QUOT']);
    }

    public function testItAcceptsJsonAsString(): void
    {
        $response = JsonResponse::fromJsonString('{"foo":"bar"}');
        var_dump('{"foo":"bar"}' === $response->getContent());
    }

    public function testSetCallbackInvalidIdentifier(): void
    {
        $response = new JsonResponse('foo');
        try {
            $response->setCallback('+invalid');
        } catch (\InvalidArgumentException $e){
            var_dump($e->getMessage() === 'The callback name is not valid.');
        }
    }

//    public function testSetContent(): void
//    {
//        $this->expectException(\InvalidArgumentException::class);
//        new JsonResponse("\xB1\x31");
//    }

//    public function testSetContentJsonSerializeError(): void
//    {
//        $this->expectException(\Exception::class);
//        $this->expectExceptionMessage('This error is expected');
//
//        $serializable = new JsonSerializableObject();
//
//        new JsonResponse($serializable);
//    }

    public function testSetComplexCallback(): void
    {
        $response = new JsonResponse(['foo' => 'bar']);
        $response->setCallback('ಠ_ಠ["foo"].bar[0]');

        var_dump('/**/ಠ_ಠ["foo"].bar[0]({"foo":"bar"});' === $response->getContent());
    }

    public function testConstructorWithNullAsDataThrowsAnUnexpectedValueException(): void
    {
        try {
            new JsonResponse(null, 200, [], true);
        } catch (\TypeError $e) {
            var_dump(stripos($e->getMessage(), 'If $json is set to true, argument $data must be a string "NULL" given.') !== false);
        }
    }

    public function testConstructorWithObjectWithToStringMethod(): void
    {
        $class = new ObjectWithToStringMethod();

        $response = new JsonResponse((string)$class, 200, [], true);

        var_dump('{}' === $response->getContent());
    }
}
