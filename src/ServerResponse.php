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
     * @return static
     */
    public function send(int $bufferSize = 8192)
    {
        foreach ($this->getHeaders() as $name => $values) {
            if (strtolower($name) !== 'set-cookie') {
                header(sprintf('%s: %s', $name, implode(',', $values)), false);
            } else {
                foreach ($values as $value) {
                    header(sprintf('%s: %s', $name, $value), false);
                }
            }
        }

        $http_line = sprintf(
            'HTTP/%s %s %s',
            $this->getProtocolVersion(),
            $this->getStatusCode(),
            $this->getReasonPhrase()
        );
        header($http_line, true, $this->getStatusCode());

        $stream = $this->getBody();
        if ($stream->isSeekable()) {
            $stream->rewind();
        }
        while (!$stream->eof()) {
            echo $stream->read($bufferSize);
        }
        return $this;
    }

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
            $headerLine .= '; expires=' . gmdate('D, d M Y H:i:s T', time() + $maxAge);
            $headerLine .= '; Max-Age=' . $maxAge;
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
