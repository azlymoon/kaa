<?php

use Kaa\HttpFoundation\Response;


/**
 * Use case for Response->__toString();
 */

//// Create a new Response object with some content
//$response = new Response('Hello, world!');
//
//// Output the response as a string using the __toString() method
//echo $response;


/**
 * Use case for Response->__clone();
 */

//$response1 = new Response('Hello, world!', Response::HTTP_OK, array('content-type' => 'text/html'));
//
//// Clone the Response object
//$response2 = clone $response1;
//
//// Modify the cloned Response object
//$response2->setContent('Goodbye, world!');
//
//// Output the original and cloned Response objects
//echo $response1->getContent(); // Output: Hello, world!
//echo $response2->getContent(); // Output: Goodbye, world!

/**
 * A simple example of how to use the Response class
 */

//$response = new Response();
//$response->setContent('Hello, world!');
//$response->setStatusCode(Response::HTTP_OK);
//$response->headers->set('Content-Type', 'text/plain');
//
//$response->send();

/**
 * TODO: Prepare Request Mime Type
 *
 * An example from Symfony/HttpFoundation docs
 */

//use Symfony\Component\HttpFoundation\Request;
//
//$response = new Response(
//    'Content',
//    Response::HTTP_OK,
//    ['content-type' => 'text/html']
//);
//
//$response->setContent('Hello World');
//
//// the headers public attribute is a ResponseHeaderBag
//$response->headers->set('Content-Type', 'text/plain');
//
//$response->setStatusCode(Response::HTTP_NOT_FOUND);
//
//$response->setCharset('ISO-8859-1');
//
//$request = Request::create(
//    '/a/b/c/d',
//    'GET',
//    ['name' => 'Fabien']
//);
//
//$response->prepare($request);
//
//$response->send();

/**
 * TODO: не понятно как работает setCookie
 * An example of setting Cookies
 */

//use Symfony\Component\HttpFoundation\Cookie;
//
//$response = new Response(
//    'Content',
//    Response::HTTP_OK,
//    ['content-type' => 'text/html']
//);
//
//$response->headers->setCookie(Cookie::create('foo', 'bar'));
//
//// or create a cookie
//$cookie = Cookie::create('foo')
//    ->withValue('bar')
//    ->withExpires(strtotime('Fri, 20-May-2011 15:25:52 GMT'))
//    ->withDomain('.example.com')
//    ->withSecure(true);

/**
 * An example of managing the HTTP Cache
 */

//$response = new Response(
//    'Content',
//    Response::HTTP_OK,
//    ['content-type' => 'text/html']
//);
//
//$response->setCache([
//    'must_revalidate'  => false,
//    'no_cache'         => false,
//    'no_store'         => false,
//    'no_transform'     => false,
//    'public'           => true,
//    'private'          => false,
//    'proxy_revalidate' => false,
//    'max_age'          => 600,
//    's_maxage'         => 600,
//    'stale_if_error'   => 86400,
//    'stale_while_revalidate' => 60,
//    'immutable'        => true,
//    'last_modified'    => new \DateTime(),
//    'etag'             => 'abcdef',
//]);
//
//if ($response->isNotModified($request)) {
//    $response->send();
//}

/**
 * An example of Redirecting the User
 */

//use Symfony\Component\HttpFoundation\RedirectResponse;
//
//$response = new RedirectResponse('http://example.com/');

/**
 * An example of Streaming a response
 */

//use Symfony\Component\HttpFoundation\StreamedResponse;
//
//$response = new StreamedResponse();
//$response->setCallback(function () {
//    var_dump('Hello World');
//    flush();
//    sleep(2);
//    var_dump('Hello World');
//    flush();
//});
//$response->send();

/**
 * An example of Serving Files
 * TODO: Разберись что это такое https://symfony.com/doc/current/components/http_foundation.html#serving-files
 */

/**
 * An example of Creating a JSON Response
 */

$response = new Response();
$response->setContent(json_encode([
    'data' => 123,
]));
$response->headers->set('Content-Type', 'application/json');

// or create it with JsonResponse class

//use Symfony\Component\HttpFoundation\JsonResponse;
//
//// if you know the data to send when creating the response
//$response = new JsonResponse(['data' => 123]);
//
//// if you don't know the data to send or if you want to customize the encoding options
//$response = new JsonResponse();
//// ...
//// configure any custom encoding options (if needed, it must be called before "setData()")
////$response->setEncodingOptions(JsonResponse::DEFAULT_ENCODING_OPTIONS | \JSON_PRESERVE_ZERO_FRACTION);
//$response->setData(['data' => 123]);
//
//// if the data to send is already encoded in JSON
//$response = JsonResponse::fromJsonString('{ "data": 123 }');

// ChatGPT example

use Kaa\HttpFoundation\JsonResponse;

$data = [
    'name' => 'John Doe',
    'age' => 30,
    'email' => 'john.doe@example.com'
];

$response = new JsonResponse($data);

echo $response->getContent(); // {"name":"John Doe","age":30,"email":"john.doe@example.com"}


/**
 * An example of Json Callback
 * TODO: Разберись что это такое https://symfony.com/doc/current/components/http_foundation.html#jsonp-callback
 */

//$response = new Response();
//$response->setCallback('handleResponse');