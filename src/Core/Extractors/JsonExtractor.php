<?php

namespace Coco\SourceWatcher\Core\Extractors;

use Coco\SourceWatcher\Core\Data\Row;
use Coco\SourceWatcher\Core\Exception\SourceWatcherException;
use Coco\SourceWatcher\Core\IO\Inputs\FileInput;
use Coco\SourceWatcher\Core\Step\Extractor;
use Coco\SourceWatcher\Utils\Internationalization;
use Flow\JSONPath\JSONPath;
use Flow\JSONPath\JSONPathException;

/**
 * Class JsonExtractor
 *
 * @package Coco\SourceWatcher\Core\Extractors
 */
class JsonExtractor extends Extractor
{
    protected array $columns = [];

    protected array $availableOptions = [ "columns" ];

    public function getColumns () : array
    {
        return $this->columns;
    }

    public function setColumns ( array $columns ) : void
    {
        $this->columns = $columns;
    }

    /**
     * @return array
     * @throws SourceWatcherException
     */
    public function extract () : array
    {
        if ( $this->input == null ) {
            throw new SourceWatcherException( Internationalization::getInstance()->getText( JsonExtractor::class,
                "No_Input_Provided" ) );
        }

        $inputIsFileInput = $this->input instanceof FileInput;

        if ( !$inputIsFileInput ) {
            throw new SourceWatcherException( sprintf( Internationalization::getInstance()->getText( JsonExtractor::class,
                "Input_Not_Instance_Of_File_Input" ), FileInput::class ) );
        }

        $this->result = [];

        $location = $this->input->getInput();
        if ( $location === null || $location === '' ) {
            throw new SourceWatcherException( sprintf( Internationalization::getInstance()->getText( JsonExtractor::class,
                "File_Input_File_Not_Found" ), (string) $location ) );
        }
        $location = (string) $location;
        $location = str_replace( '\\/', '/', $location );

        $isUrl = $this->isUrl( $location );
        if ( !$isUrl && !file_exists( $location ) ) {
            throw new SourceWatcherException( sprintf( Internationalization::getInstance()->getText( JsonExtractor::class,
                "File_Input_File_Not_Found" ), $location ) );
        }

        $raw = @file_get_contents( $location );
        if ( $raw === false ) {
            throw new SourceWatcherException( $isUrl
                ? 'Failed to fetch JSON from URL. Ensure allow_url_fopen is enabled.'
                : sprintf( Internationalization::getInstance()->getText( JsonExtractor::class, "File_Input_File_Not_Found" ), $location ) );
        }
        $data = json_decode( $raw, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            throw new SourceWatcherException(
                sprintf( 'Invalid JSON in %s: %s', $location, json_last_error_msg() )
            );
        }

        if ( !is_array( $data ) ) {
            throw new SourceWatcherException(
                sprintf( 'JSON root must be an array or object in %s', $location )
            );
        }

        if ( $this->columns ) {
            $jsonPath = new JSONPath( $data );

            try {
                foreach ( $this->columns as $key => $path ) {
                    $this->columns[$key] = $jsonPath->find( $path )->getData();
                }
            } catch ( JSONPathException $jsonPathException ) {
                throw new SourceWatcherException( sprintf( Internationalization::getInstance()->getText( JsonExtractor::class,
                    "JSON_Path_Exception" ), $jsonPathException->getMessage() ) );
            }

            $data = $this->transpose( $this->columns );
        }

        foreach ( $data as $row ) {
            array_push( $this->result, new Row( $this->normalizeRowForOutput( $row ) ) );
        }

        return $this->result;
    }

    /**
     * Ensure row values are scalar or null so they never become "Array" when stored (e.g. in DB).
     *
     * @param array<string, mixed> $row
     * @return array<string, string|int|float|bool|null>
     */
    private function normalizeRowForOutput ( array $row ) : array
    {
        $out = [];
        foreach ( $row as $key => $value ) {
            if ( $value === null || is_scalar( $value ) ) {
                $out[$key] = $value;
            } else {
                $encoded = json_encode( $value, JSON_UNESCAPED_UNICODE );
                $out[$key] = ( $encoded !== false ) ? $encoded : 'null';
            }
        }
        return $out;
    }

    private function isUrl ( string $location ) : bool
    {
        return str_starts_with( $location, 'http://' ) || str_starts_with( $location, 'https://' );
    }

    private function transpose ( $columns ) : array
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
