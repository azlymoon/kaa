<?php

namespace Kaa\HttpKernel\Response;

use JsonEncoder;
use JsonException;
use Kaa\HttpKernel\HttpCode;

class JsonResponse extends Response
{
    /**
     * {@inheritDoc}
     *
     * @param string|null $content Json string
     */
    public function __construct(?string $content = '', int $status = HttpCode::HTTP_OK, array $headers = [])
    {
        $headers[] = 'Content-Type: application/json';
        parent::__construct($content, $status, $headers);
    }

    /**
     * @param string[] $headers
     * @throws JsonException
     */
    public static function fromObject(object $data, int $status = HttpCode::HTTP_OK, array $headers = []): self
    {
        $json = JsonEncoder::encode($data);
        if ($json === '' && JsonEncoder::getLastError() !== '') {
            throw new JsonException(JsonEncoder::getLastError());
        }
        return new self($json, $status, $headers);
    }
}
