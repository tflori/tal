<?php

namespace Tal;

use Tal\Psr7Extended\ServerResponseInterface;

class ServerResponse extends Response implements ServerResponseInterface
{
    use ChangeableMessageTrait;

    /**
     * Sets the specified status code and, optionally, reason phrase.
     *
     * If no reason phrase is specified, implementations MAY choose to default
     * to the RFC 7231 or IANA recommended reason phrase for the response's
     * status code.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @param int $code The 3-digit integer result code to set.
     * @param string $reasonPhrase The reason phrase to use with the
     *     provided status code; if none is provided, implementations MAY
     *     use the defaults as suggested in the HTTP specification.
     * @return static
     * @throws \InvalidArgumentException For invalid status code arguments.
     */
    public function setStatus($code, $reasonPhrase = '')
    {
        $this->statusCode = (int) $code;
        if ($reasonPhrase == '' && isset(static::$phrases[$this->statusCode])) {
            $reasonPhrase = static::$phrases[$this->statusCode];
        }
        $this->reasonPhrase = $reasonPhrase;
        return $this;
    }

    /**
     * Sends this response to the client.
     *
     * @return static
     */
    public function send()
    {
        $http_line = sprintf(
            'HTTP/%s %s %s',
            $this->getProtocolVersion(),
            $this->getStatusCode(),
            $this->getReasonPhrase()
        );
        header($http_line, true, $this->getStatusCode());

        foreach ($this->getHeaders() as $name => $values) {
            if (strtolower($name) !== 'set-cookie') {
                header(sprintf('%s: %s', $name, implode(',', $values)), false);
            } else {
                foreach ($values as $value) {
                    header(sprintf('%s: %s', $name, $value), false);
                }
            }
        }

        $stream = $this->getBody();
        if ($stream->isSeekable()) {
            $stream->rewind();
        }
        while (!$stream->eof()) {
            echo $stream->read(1024 * 8);
        }
        return $this;
    }

    public function setCookie(
        $name,
        $value = "",
        $expire = 0,
        $path = "",
        $domain = "",
        $secure = false,
        $httponly = false
    ) {
        $headerLine = sprintf('%s=%s', $name, urlencode($value));
        if ($expire) {
            $headerLine .= '; expires=' . gmdate('D, d M Y H:i:s T', time() + $expire);
            $headerLine .= '; max-age=' . $expire;
        }
        // @todo prepare the header with all options given
        $this->addHeader('Set-Cookie', $headerLine);
        return $this;
    }

    public function deleteCookie($name)
    {
        $this->setCookie($name, 'deleted', -1);
        return $this;
    }
}
