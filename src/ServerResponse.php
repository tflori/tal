<?php

namespace Tal;

use Psr\Http\Message\StreamInterface;
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
