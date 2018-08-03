<?php

use Psr\Http\Message\ResponseInterface;

if (!function_exists('sendResponse')) {
    function sendResponse(ResponseInterface $response)
    {
        $http_line = sprintf('HTTP/%s %s %s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        );
        header($http_line, true, $response->getStatusCode());

        foreach ($response->getHeaders() as $name => $values) {
            if (strtolower($name) !== 'set-cookie') {
                header(sprintf('%s: %s', $name, implode(',', $values)), false);
            } else {
                foreach ($values as $value) {
                    header(sprintf('%s: %s', $name, $value), false);
                }
            }
        }

        $stream = $response->getBody();
        if ($stream->isSeekable()) {
            $stream->rewind();
        }
        while (!$stream->eof()) {
            echo $stream->read(1024 * 8);
        }
    }
}