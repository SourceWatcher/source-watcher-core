<?php

namespace Coco\SourceWatcher\Core\Api;

use Coco\SourceWatcher\Core\SourceWatcherException;
use Coco\SourceWatcher\Utils\Internationalization;

/**
 * Class ApiReader
 *
 * When you describe the endpoint, you list the end path only (hence the term "end point").
 * The full path that contains both the base path and the endpoint is often called a resource URL.
 *
 * @package Coco\SourceWatcher\Core\Api
 */
class ApiReader implements Reader
{
    protected ?string $resourceURL = null;

    protected int $timeout = 5;

    protected array $headers = [];

    protected int $currentAttempt;

    protected int $attempts = 3;

    public function __construct ()
    {
        $this->currentAttempt = 1;
    }

    public function getResourceURL () : string
    {
        return $this->resourceURL;
    }

    public function setResourceURL ( string $resourceURL ) : void
    {
        $this->resourceURL = $resourceURL;
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

    /**
     * @return bool|mixed|string
     * @throws SourceWatcherException
     */
    public function read ()
    {
        if ( $this->resourceURL == null || $this->resourceURL == "" ) {
            throw new SourceWatcherException( Internationalization::getInstance()->getText( ApiReader::class,
                "No_Resource_URL_Found" ) );
        }

        $curl = curl_init();

        curl_setopt( $curl, CURLOPT_URL, $this->resourceURL );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, $this->timeout );

        if ( !empty( $this->headers ) ) {
            curl_setopt( $curl, CURLOPT_HTTPHEADER, $this->headers );
        }

        /**
         * https://www.php.net/manual/en/function.curl-exec.php
         *
         * Returns TRUE on success or FALSE on failure.
         * However, if the CURLOPT_RETURNTRANSFER option is set, it will return the result on success, FALSE on failure.
         */

        $response = curl_exec( $curl );

        if ( !$response && $this->currentAttempt < $this->attempts ) {
            $this->currentAttempt++;

            return $this->read();
        }

        return $response;
    }
}
