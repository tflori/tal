<?php

namespace Tal\Test;

use PHPUnit\Framework\TestCase;
use Tal\ServerResponse;

class ServerResponseTest extends TestCase
{
    public function testSetCookieAddsHeader()
    {
        $response = new ServerResponse();

        $response->setCookie('foo', 'bar');

        self::assertEquals([
            'Set-Cookie' => ['foo=bar']
        ], $response->getHeaders());
    }
}
