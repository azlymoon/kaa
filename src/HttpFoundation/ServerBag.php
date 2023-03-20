<?php

namespace Kaa\HttpFoundation;

/**
 * ServerBag is a container for HTTP headers from the $_SERVER variable.
 */
class ServerBag extends ParameterBag
{
    /**
     * Gets the HTTP headers.
     * @return mixed
     */
    public function getHeaders()
    {
        // Да, $headers действительно нужно выбрать правильный тип. Но пувсть пока так
        /** @var mixed $headers */
        $headers = [];
        foreach ($this->parameters as $key => $value) {
            // $key is always string
            $key = (string)$key;
            if (str_starts_with($key, 'HTTP_')) {
                $headers[substr($key, 5)] = $value;
            } elseif (\in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'], true)) {
                $headers[$key] = $value;
            }
        }

        return $headers;
    }
}
