<?php

namespace Kaa\HttpFoundation;

use JsonEncoder;
use Kaa\HttpKernel\Exception\JsonException;

/**
 * Response represents an HTTP response in JSON format.
 *
 * Note that this class does not force the returned JSON content to be an
 * object. It is however recommended that you do return an object as it
 * protects yourself against XSSI and JSON-JavaScript Hijacking.
 *
 * @see https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/AJAX_Security_Cheat_Sheet.md#always-return-json-with-an-object-on-the-outside
 *
 * @author Igor Wiedler <igor@wiedler.ch>
 */
class JsonResponse extends Response
{
    /** @var mixed $data */
    protected $data;

    /** @var ?string $callback */
    protected ?string $callback;

    // Encode <, >, ', &, and " characters in the JSON, making it also safe to be embedded into HTML.
    // 15 === JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
    public const DEFAULT_ENCODING_OPTIONS = 15;

    protected int $encodingOptions = self::DEFAULT_ENCODING_OPTIONS;

    /**
     * @param bool $json If the data is already a JSON string
     * @param mixed $headers
     * @param mixed $data
     */
    public function __construct($data = null, int $status = 200, $headers = [], bool $json = false)
    {
        parent::__construct('', $status, $headers);

        # If json is passed, it is a string
        $json ? $this->setJson((string)$data) : $this->setData($data);
    }

    /**
     * @param string[] $headers
     * @throws JsonException
     */
    public static function fromObject(object $data, int $status = 200, array $headers = []): self
    {
        $json = JsonEncoder::encode($data);
        if ($json === '' && JsonEncoder::getLastError() !== '') {
            throw new JsonException(JsonEncoder::getLastError());
        }
        return new self($json, $status, $headers, true);
    }

    /**
     * Factory method for chainability.
     *
     * Example:
     *
     *     return JsonResponse::fromJsonString('{"key": "value"}')
     *         ->setSharedMaxAge(300);
     *
     * @param string $data    The JSON response string
     * @param int    $status  The response status code (200 "OK" by default)
     * @param any[] $headers An array of response headers
     */
    public static function fromJsonString(string $data, int $status = 200, $headers = []): static
    {
        return new static($data, $status, $headers, true);
    }

    /**
     * Sets the JSONP callback.
     *
     * @param string|null $callback The JSONP callback or null to use none
     *
     * @return $this
     *
     * @throws \InvalidArgumentException When the callback name is not valid
     */
    public function setCallback(?string $callback = null): Response
    {
        if (null !== $callback) {
            // partially taken from https://geekality.net/2011/08/03/valid-javascript-identifier/
            // partially taken from https://github.com/willdurand/JsonpCallbackValidator
            //      JsonpCallbackValidator is released under the MIT License. See https://github.com/willdurand/JsonpCallbackValidator/blob/v1.1.0/LICENSE for details.
            //      (c) William Durand <william.durand1@gmail.com>
            $pattern = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*(?:\[(?:"(?:\\\.|[^"\\\])*"|\'(?:\\\.|[^\'\\\])*\'|\d+)\])*?$/u';
            $reserved = [
                'break', 'do', 'instanceof', 'typeof', 'case', 'else', 'new', 'var', 'catch', 'finally', 'return', 'void', 'continue', 'for', 'switch', 'while',
                'debugger', 'function', 'this', 'with', 'default', 'if', 'throw', 'delete', 'in', 'try', 'class', 'enum', 'extends', 'super',  'const', 'export',
                'import', 'implements', 'let', 'private', 'public', 'yield', 'interface', 'package', 'protected', 'static', 'null', 'true', 'false',
            ];
            $parts = explode('.', $callback);
            foreach ($parts as $part) {
                if (!preg_match($pattern, $part) || \in_array($part, $reserved, true)) {
                    throw new \InvalidArgumentException('The callback name is not valid.');
                }
            }
        }

        $this->callback = $callback;

        return $this->update();
    }

    /**
     * Sets a raw string containing a JSON document to be sent.
     */
    public function setJson(string $json): Response
    {
        $this->data = $json;

        return $this->update();
    }

    /**
     * TODO: напиши, что не должно быть комментариев
     * Sets the data to be sent as JSON.
     *
     * @param mixed $data
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function setData($data): Response
    {
        try {
//            $data = json_encode($data, $this->encodingOptions);
            $data = JsonEncoder::encode($data);
        } catch (\Exception $e) {
            if (str_starts_with($e->getMessage(), 'Failed calling ')) {
                throw $e;
            }
            throw $e;
        }
        return $this->setJson((string)$data);
    }

//    TODO: проблема с JsonEncode() - он похоже не принмает параметры, спроси ребят
//    /**
//     * Sets options used while encoding data to JSON.
//     */
//    public function setEncodingOptions(int $encodingOptions): Response
//    {
//        $this->encodingOptions = $encodingOptions;
//
//        return $this->setData(json_decode($this->data));
//    }

    /**
     * Sets options used while encoding data to JSON.
     */
    public function setEncodingOptions(int $encodingOptions): Response
    {
        $this->encodingOptions = $encodingOptions;

        return $this->setData(json_decode($this->data));
    }

    /**
     * Updates the content and headers according to the JSON data and callback.
     *
     */
    protected function update(): Response
    {
        if (null !== $this->callback) {
            // Not using application/javascript for compatibility reasons with older browsers.
            $this->headers->set('Content-Type', 'text/javascript');

            return $this->setContent(sprintf('/**/%s(%s);', $this->callback, $this->data));
        }

        // Only set the header when there is none or when it equals 'text/javascript'
        // (from a previous update with callback) in order to not overwrite a custom definition.
        if (!$this->headers->has('Content-Type') || 'text/javascript' === $this->headers->get('Content-Type')) {
            $this->headers->set('Content-Type', 'application/json');
        }

        return $this->setContent((string)$this->data);
    }
}
