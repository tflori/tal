<?php

namespace Tal;

use Tal\Psr7Extended\ServerResponseInterface;

class ServerResponse extends Response implements ServerResponseInterface
{
    use ChangeableMessageTrait;

    /** {@inheritDoc} */
    public function setStatus($code, $reasonPhrase = '')
    {
        return parent::setStatus($code, $reasonPhrase);
    }

    /**
     * Sends this response to the client.
     *
     * @param int $bufferSize Send maximum this amount of bytes.
     * @param Server $server For testing proposes you can provide a Server object
     * @return static
     */
    public function send(int $bufferSize = 8192, Server $server = null)
    {
        $server = $server ?? new Server();
        foreach ($this->getHeaders() as $name => $values) {
            if (strtolower($name) !== 'set-cookie') {
                $server->header(sprintf('%s: %s', $name, implode(',', $values)), false);
            } else {
                foreach ($values as $value) {
                    $server->header(sprintf('%s: %s', $name, $value), false);
                }
            }
        }

        $httpLine = sprintf(
            'HTTP/%s %s %s',
            $this->getProtocolVersion(),
            $this->getStatusCode(),
            $this->getReasonPhrase()
        );
        $server->header($httpLine, true, $this->getStatusCode());

        $stream = $this->getBody();
        if ($stream->isSeekable()) {
            $stream->rewind();
        }
        while (!$stream->eof()) {
            $server->echo($stream->read($bufferSize));
        }
        return $this;
    }

    /**
     * Returns an instance with the Set-Cookie header.
     *
     * Instead of providing a timestamp it expects an max age in seconds.
     *
     * @link http://php.net/manual/en/function.setcookie.php
     * @param $name
     * @param string $value
     * @param int $maxAge
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     * @param bool $sameSite
     * @return ServerResponse
     */
    public function withSetCookie(
        $name,
        $value = "",
        $maxAge = 0,
        $path = "",
        $domain = "",
        $secure = false,
        $httponly = false,
        $sameSite = false
    ) {
        $new = clone $this;
        return $new->setCookie($name, $value, $maxAge, $path, $domain, $secure, $httponly, $sameSite);
    }

    /**
     * Adds a Set-Cookie header.
     *
     * Instead of providing a timestamp it expects an max age in seconds.
     *
     * @link http://php.net/manual/en/function.setcookie.php
     * @param $name
     * @param string $value
     * @param int $maxAge
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     * @param bool $sameSite
     * @return $this
     */
    public function setCookie(
        $name,
        $value = "",
        $maxAge = 0,
        $path = "",
        $domain = "",
        $secure = false,
        $httponly = false,
        $sameSite = false
    ) {
        if (preg_match('/[=,; \t\r\n\013\014]/', $name)) {
            throw new \InvalidArgumentException(
                'Cookie names cannot contain any of the following \'=,; \t\r\n\013\014\''
            );
        }

        $headerLine = sprintf('%s=%s', $name, urlencode($value));

        if ($maxAge) {
            $headerLine .= '; expires=' . gmdate('r', time() + $maxAge);
            $headerLine .= '; Max-Age=' . max($maxAge, 0);
        }

        if ($path) {
            $headerLine .= '; path=' . $path;
        }

        if ($domain) {
            $headerLine .= '; domain=' . $domain;
        }

        if ($secure) {
            $headerLine .= '; secure';
        }

        if ($httponly) {
            $headerLine .= '; HttpOnly';
        }

        if ($sameSite) {
            $headerLine .= '; SameSite=strict';
        }

        $this->addHeader('Set-Cookie', $headerLine);
        return $this;
    }

    public function withDeleteCookie($name)
    {
        $new = clone $this;
        return $new->deleteCookie($name);
    }

    public function deleteCookie($name)
    {
        $this->setCookie($name, 'deleted', -1);
        return $this;
    }
}
