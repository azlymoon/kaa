<?php

namespace Kaa\HttpKernel;

class Request
{
    private string $route;

    private mixed $get;

    private mixed $post;

    private string $phpInput;

    private mixed $server;

    /**
     * @var string[]
     */
    private array $queryParameters;

    private string $method;

    private mixed $cookies;

    /** @var string[] */
    private array $urlParams;

    /**
     * @param string[] $queryParameters
     * @param string[] $urlParams
     */
    public function __construct(
        string $route = '/',
        mixed $get = [],
        mixed $post = [],
        string $phpInput = '',
        mixed $server = [],
        array $queryParameters = [],
        string $method = 'GET',
        mixed $cookies = [],
        array $urlParams = []
    ) {
        $this->route = $route;
        $this->get = $get;
        $this->post = $post;
        $this->phpInput = $phpInput;
        $this->server = $server;
        $this->queryParameters = $queryParameters;
        $this->method = $method;
        $this->cookies = $cookies;
        $this->urlParams = $urlParams;
    }

    public static function initFromGlobals(): static
    {
        return new self(
            static::parseRouteFromServer(),
            $_GET,
            $_POST,
            not_false(file_get_contents('php://input')),
            $_SERVER,
            [],
            (string)$_SERVER['REQUEST_METHOD'],
            $_COOKIE,
        );
    }

    private static function parseRouteFromServer(): string
    {
        #ifndef KPHP
        $route = rtrim((string)($_SERVER['PATH_INFO'] ?? '/'), '/');
        return $route ?: '/';
        #endif

        $route = rtrim((string)($_SERVER['SCRIPT_URL'] ?? '/'), '/');
        return $route ?: '/';
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function getParam(string $name): ?string
    {
        if (!array_key_exists($name, $this->get)) {
            return null;
        }

        return (string)$this->get[$name];
    }

    public function postParam(string $name): ?string
    {
        if (!array_key_exists($name, $this->post)) {
            return null;
        }

        return (string)$this->post[$name];
    }

    public function getPhpInput(): string
    {
        return $this->phpInput;
    }

    public function serverParam(string $name): ?string
    {
        if (!array_key_exists($name, $this->server)) {
            return null;
        }

        return (string)$this->server[$name];
    }

    public function queryParam(string $name): ?string
    {
        return $this->queryParameters[$name] ?? null;
    }

    public function addQueryParam(string $name, string $value): self
    {
        $this->queryParameters[$name] = $value;

        return $this;
    }

    public function input(): string
    {
        return $this->phpInput;
    }

    public function cookie(string $name): ?string
    {
        if (!array_key_exists($name, $this->cookies)) {
            return null;
        }

        return (string)$this->cookies[$name];
    }

    public function method(): string
    {
        return $this->method;
    }

    /** @return string[] */
    public function urlParams(): array
    {
        return $this->urlParams;
    }

    /** @param string[] $urlParams */
    public function addUrlParams(array $urlParams): void
    {
        $this->urlParams += $this->urlParams;
    }
}
