<?php

namespace Coco\SourceWatcher\Core\Extractors;

use Coco\SourceWatcher\Core\Api\ApiReader;
use Coco\SourceWatcher\Core\Data\Row;
use Coco\SourceWatcher\Core\Exception\SourceWatcherException;
use Coco\SourceWatcher\Core\IO\Inputs\ApiInput;
use Coco\SourceWatcher\Core\Step\Extractor;
use Flow\JSONPath\JSONPath;
use Flow\JSONPath\JSONPathException;
use SimpleXMLElement;

/**
 * Extractor that fetches from a URL via ApiReader and produces rows.
 * Input must be ApiInput (resource URL). Response format is set by option "response_type" (snake_case):
 * - "json" (default): parse as JSON; optional "columns" (JSONPath) like JsonExtractor.
 * - "xml": parse as XML; root's repeated child elements become rows (child tag names → keys, text content → values).
 *
 * @package Coco\SourceWatcher\Core\Extractors
 */
class ApiExtractor extends Extractor
{
    protected array $columns = [];

    /** @var string "json" | "xml" */
    protected string $responseType = "json";

    protected array $availableOptions = [ "columns", "responseType" ];

    public function getColumns () : array
    {
        return $this->columns;
    }

    public function setColumns ( array $columns ) : void
    {
        $this->columns = $columns;
    }

    public function getResponseType () : string
    {
        return $this->responseType;
    }

    public function setResponseType ( string $responseType ) : void
    {
        $this->responseType = $responseType;
    }

    /**
     * @return array
     * @throws SourceWatcherException
     */
    public function extract () : array
    {
        if ( $this->input === null ) {
            throw new SourceWatcherException( "No input provided." );
        }

        if ( !$this->input instanceof ApiInput ) {
            throw new SourceWatcherException( sprintf( "Input must be an instance of %s.", ApiInput::class ) );
        }

        $url = $this->input->getInput();
        if ( $url === null || $url === "" ) {
            throw new SourceWatcherException( "No resource URL provided." );
        }

        $reader = new ApiReader();
        $reader->setResourceURL( $url );
        $reader->setTimeout( $this->input->getTimeout() );
        if ( !empty( $this->input->getHeaders() ) ) {
            $reader->setHeaders( $this->input->getHeaders() );
        }

        $response = $reader->read();
        if ( $response === false ) {
            throw new SourceWatcherException( "API request failed for URL: " . $url );
        }

        $data = $this->parseResponse( $response );
        $this->result = [];

        if ( $this->responseType === "json" && !empty( $this->columns ) ) {
            $jsonPath = new JSONPath( $data );
            try {
                $pathResults = [];
                foreach ( $this->columns as $key => $path ) {
                    $pathResults[$key] = $jsonPath->find( $path )->getData();
                }
                $data = $this->transpose( $pathResults );
            } catch ( JSONPathException $e ) {
                throw new SourceWatcherException( sprintf( "JSON Path error: %s", $e->getMessage() ) );
            }
        } elseif ( $this->responseType === "json" ) {
            if ( isset( $data[0] ) && is_array( $data[0] ) ) {
                $data = $data;
            } elseif ( is_array( $data ) ) {
                $data = [ $data ];
            } else {
                $data = [ [ "value" => $data ] ];
            }
        }

        foreach ( $data as $row ) {
            $this->result[] = new Row( is_array( $row ) ? $row : [ "value" => $row ] );
        }

        return $this->result;
    }

    /**
     * @return array|mixed Parsed data: array of rows for XML; decoded structure for JSON
     * @throws SourceWatcherException
     */
    private function parseResponse ( string $response ) : mixed
    {
        $type = strtolower( $this->responseType );
        if ( $type === "xml" ) {
            return $this->xmlToRows( $response );
        }
        $data = json_decode( $response, true );
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            throw new SourceWatcherException( "Invalid JSON from API: " . json_last_error_msg() );
        }
        return $data;
    }

    /**
     * Converts XML string to array of rows. Expects root to have repeated child elements (e.g. <users><user>...</user></users>).
     * Each such child becomes one row; its direct child tags become keys with text content as values.
     *
     * @throws SourceWatcherException
     */
    private function xmlToRows ( string $xmlString ) : array
    {
        $prev = libxml_use_internal_errors( true );
        $xml = simplexml_load_string( $xmlString );
        libxml_use_internal_errors( $prev );
        if ( $xml === false ) {
            $msg = libxml_get_last_error();
            throw new SourceWatcherException( "Invalid XML from API: " . ( $msg ? $msg->message : "parse error" ) );
        }
        $root = $xml->children();
        if ( $root->count() === 0 ) {
            return [];
        }
        $rows = [];
        foreach ( $root as $element ) {
            $rows[] = $this->xmlElementToRow( $element );
        }
        return $rows;
    }

    private function xmlElementToRow ( SimpleXMLElement $element ) : array
    {
        $row = [];
        foreach ( $element->children() as $name => $child ) {
            $row[$name] = (string) $child;
        }
        return $row;
    }

    private function transpose ( array $columns ) : array
    {
        $data = [];
        foreach ( $columns as $column => $items ) {
            foreach ( $items as $row => $item ) {
                $data[$row][$column] = $item;
            }
        }
        return $data;
    }
}
