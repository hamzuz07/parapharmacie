<?php

namespace WooZoneVendor\GuzzleHttp\Exception;

use WooZoneVendor\Psr\Http\Message\StreamInterface;
/**
 * Exception thrown when a seek fails on a stream.
 */
class SeekException extends \RuntimeException implements \WooZoneVendor\GuzzleHttp\Exception\GuzzleException
{
    private $stream;
    public function __construct(\WooZoneVendor\Psr\Http\Message\StreamInterface $stream, $pos = 0, $msg = '')
    {
        $this->stream = $stream;
        $msg = $msg ?: 'Could not seek the stream to position ' . $pos;
        parent::__construct($msg);
    }
    /**
     * @return StreamInterface
     */
    public function getStream()
    {
        return $this->stream;
    }
}
