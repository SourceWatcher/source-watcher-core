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

        if ( !file_exists( $this->input->getInput() ) ) {
            throw new SourceWatcherException( sprintf( Internationalization::getInstance()->getText( JsonExtractor::class,
                "File_Input_File_Not_Found" ), $this->input->getInput() ) );
        }

        $data = json_decode( file_get_contents( $this->input->getInput() ), true );

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
            array_push( $this->result, new Row( $row ) );
        }

        return $this->result;
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
