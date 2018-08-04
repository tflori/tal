<?php

namespace Tal\Psr7Extended;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface ClientResponseInterface
 *
 * This interface extends the response just to divide the ClientResponseInterface from
 * the ServerResponseInterface.
 *
 * @package Tal\Psr7Extended
 */
interface ClientResponseInterface extends ResponseInterface
{
}
