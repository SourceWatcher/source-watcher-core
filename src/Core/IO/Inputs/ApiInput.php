<?php

namespace Coco\SourceWatcher\Core\IO\Inputs;

/**
 * Input for API resources: holds the resource URL (and optional timeout/headers for the reader).
 * Does not assume a response format; the consumer (e.g. ApiExtractor) decides how to interpret
 * the body via its "responseType" option ("json" or "xml").
 *
 * @package Coco\SourceWatcher\Core\IO\Inputs
 */
class ApiInput extends Input
{
    private ?string $resourceURL = null;

    private int $timeout = 10;

    private array $headers = [];

    public function __construct ( ?string $resourceURL = null, int $timeout = 10, array $headers = [] )
    {
        $this->resourceURL = $resourceURL;
        $this->timeout = $timeout;
        $this->headers = $headers;
    }

    public function getInput ()
    {
        return $this->resourceURL;
    }

    public function setInput ( $input )
    {
        $this->resourceURL = $input;
    }

    public function getTimeout () : int
    {
        return $this->timeout;
    }

    public function setTimeout ( int $timeout ) : void
    {
        $this->timeout = $timeout;
    }

    public function getHeaders () : array
    {
        return $this->headers;
    }

    public function setHeaders ( array $headers ) : void
    {
        $this->headers = $headers;
    }
}
