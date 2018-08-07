<?php

namespace Tal\Test;

use GuzzleHttp\Psr7\Stream;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Tal\Server;
use Tal\ServerResponse;
use Mockery as m;

class ServerResponseTest extends MockeryTestCase
{
    /** @var Server|m\Mock */
    protected $server;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->server = m::mock(Server::class);
        $this->server->shouldReceive('header')->byDefault();
        $this->server->shouldReceive('echo')->byDefault();
    }

    public function testSetStatusIsPublic()
    {
        $response = new ServerResponse();

        $response->setStatus(404);

        self::assertSame(404, $response->getStatusCode());
    }

    public function testSetCookieAddsHeader()
    {
        $response = new ServerResponse();

        $response->setCookie('foo', 'bär', 3600, '/', 'localhost', true, true, true);

        self::assertEquals([
            'Set-Cookie' => [
                'foo=b%C3%A4r' .
                '; expires=' . gmdate('r', time()+3600) . '; Max-Age=3600' .
                '; path=/' .
                '; domain=localhost' .
                '; secure; HttpOnly; SameSite=strict',
            ]
        ], $response->getHeaders());
    }

    public function testCookieNameHasToBeValid()
    {
        $response = new ServerResponse();

        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage('Cookie names cannot contain');

        $response->setCookie('a=b', 'foo');
    }

    public function testWithCookieChangesClone()
    {
        $response = new ServerResponse();

        $clone = $response->withSetCookie('foo', 'bar');

        self::assertNotSame($response, $clone);
        self::assertEmpty($response->getHeaders());
        self::assertArrayHasKey('Set-Cookie', $clone->getHeaders());
    }

    public function testDeleteCookieHeader()
    {
        $response = new ServerResponse();

        $response->deleteCookie('foo');

        self::assertEquals([
            'Set-Cookie' => [
                'foo=deleted' .
                '; expires=' . gmdate('r', time()-1) . '; Max-Age=0',
            ]
        ], $response->getHeaders());
    }

    public function testWithDeleteCookie()
    {
        $response = new ServerResponse();

        $clone = $response->withDeleteCookie('foo');

        self::assertNotSame($response, $clone);
        self::assertEmpty($response->getHeaders());
    }

    public function testSendsHeaderline()
    {
        $response = new ServerResponse(404);
        $response->setProtocolVersion('1.0');

        $this->server->shouldReceive('header')->with('HTTP/1.0 404 Not Found', true, 404)
            ->once();

        $response->send(8192, $this->server);
    }

    public function testRewindsStream()
    {
        $stream = m::mock(new Stream(fopen('php://memory', 'w+')));
        $response = new ServerResponse(200, [], $stream);

        $stream->shouldReceive('rewind')->with()
            ->once();

        $response->send(8192, $this->server);
    }

    public function testSendsHeadersBeforeStatusLine()
    {
        $response = new ServerResponse();
        $response->addHeader('Content-Type', 'text/html');

        $this->server->shouldReceive('header')->with('Content-Type: text/html', false)->once()->ordered();
        $this->server->shouldReceive('header')->with('HTTP/1.1 200 OK', true, 200)->once()->ordered();

        $response->send(8192, $this->server);
    }

    public function testMultipleHeaders()
    {
        $response = new ServerResponse();
        $response->addHeader('Vary', 'User-Agent');
        $response->addHeader('Vary', 'Accept');

        $this->server->shouldReceive('header')->with('Vary: User-Agent,Accept', false)
            ->once();

        $response->send(8192, $this->server);
    }

    public function testMultipleSetCookieHeaders()
    {
        $response = new ServerResponse();
        $response->setCookie('foo', 'bar');
        $response->setCookie('sid', 'abc');

        $this->server->shouldReceive('header')->with(m::pattern('/^Set-Cookie: /'), false)->twice();

        $response->send(8192, $this->server);
    }
}
