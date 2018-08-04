<?php


namespace Tal\Psr7Extended;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

interface ServerResponseInterface extends ResponseInterface
{
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
    public function setStatus($code, $reasonPhrase = '');

    /**
     * Sets the specified HTTP protocol version.
     *
     * The version string MUST contain only the HTTP version number (e.g.,
     * "1.1", "1.0").
     *
     * @param string $version HTTP protocol version
     * @return static
     */
    public function setProtocolVersion($version);

    /**
     * Sets the provided value replacing the specified header.
     *
     * While header names are case-insensitive, the casing of the header will
     * be preserved by this function, and returned from getHeaders().
     *
     * @param string $name Case-insensitive header field name.
     * @param string|string[] $value Header value(s).
     * @return static
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function setHeader($name, $value);

    /**
     * Adds the specified header appended with the given value.
     *
     * Existing values for the specified header will be maintained. The new
     * value(s) will be appended to the existing list. If the header did not
     * exist previously, it will be added.
     *
     * @param string $name Case-insensitive header field name to add.
     * @param string|string[] $value Header value(s).
     * @return static
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function addHeader($name, $value);

    /**
     * Deletes the specified header.
     *
     * Header resolution MUST be done without case-sensitivity.
     *
     * @param string $name Case-insensitive header field name to remove.
     * @return static
     */
    public function deleteHeader($name);

    /**
     * Sets the specified message body.
     *
     * The body MUST be a StreamInterface object.
     *
     * @param StreamInterface $body Body.
     * @return static
     * @throws \InvalidArgumentException When the body is not valid.
     */
    public function setBody(StreamInterface $body);

    /**
     * Sends this response to the client.
     *
     * @return static
     */
    public function send();
}
