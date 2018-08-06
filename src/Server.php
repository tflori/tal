<?php

namespace Tal;

/**
 * Class Server
 *
 * This class is a stupid wrapper for php functions for testing.
 *
 * @package Tal
 * @author Thomas Flori <thflori@gmail.com>
 * @codeCoverageIgnore trivial
 */
class Server
{
    /**
     * Send a raw HTTP header
     *
     * @link http://php.net/manual/en/function.header.php
     * @param string $string The header string
     * @param bool $replace
     * @param int $responseCode
     * @return void
     */
    public function header($string, $replace = true, $responseCode = null)
    {
        header($string, $replace, $responseCode);
    }

    /**
     * Echos the given $string
     *
     * @param $string
     * @return void
     */
    public function echo($string)
    {
        echo($string);
    }
}
