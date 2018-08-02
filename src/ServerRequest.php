<?php

namespace Tal;

use GuzzleHttp\Psr7\ServerRequest as BaseServerRequest;

class ServerRequest extends BaseServerRequest
{
    public static function fromGlobals()
    {
        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $uri = self::getUriFromGlobals();
        $body = new LazyOpenStream('php://input', 'r+');
        $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? str_replace('HTTP/', '', $_SERVER['SERVER_PROTOCOL']) : '1.1';

        $serverRequest = new static($method, $uri, $headers, $body, $protocol, $_SERVER);

        return $serverRequest
            ->withCookieParams($_COOKIE)
            ->withQueryParams($_GET)
            ->withParsedBody($_POST)
            ->withUploadedFiles(self::normalizeFiles($_FILES));
    }

    public function getCookie(string $name, $default = null)
    {
        return $this->getCookieParams()[$name] ?? $default;
    }

    public function hasCookie(string $name)
    {
        return isset($this->getCookieParams()[$name]);
    }
}
