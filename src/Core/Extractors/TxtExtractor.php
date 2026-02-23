<?php

namespace Coco\SourceWatcher\Core\Extractors;

use Coco\SourceWatcher\Core\Data\Row;
use Coco\SourceWatcher\Core\Exception\SourceWatcherException;
use Coco\SourceWatcher\Core\IO\Inputs\FileInput;
use Coco\SourceWatcher\Core\Step\Extractor;

class TxtExtractor extends Extractor
{
    protected string $column;

    protected array $availableOptions = [ "column" ];

    public function __construct ()
    {
        $this->column = "";
    }

    /**
     * @return string
     */
    public function getColumn () : string
    {
        return $this->column;
    }

    /**
     * @param string $column
     */
    public function setColumn ( string $column ) : void
    {
        $this->column = $column;
    }

    /**
     * @return array
     * @throws SourceWatcherException
     */
    public function extract () : array
    {
        if ( $this->input == null ) {
            throw new SourceWatcherException( "An input must be provided." );
        }

        $inputIsFileInput = $this->input instanceof FileInput;

        if ( !$inputIsFileInput ) {
            throw new SourceWatcherException( sprintf( "The input must be an instance of %s", FileInput::class ) );
        }

        $this->result = [];

        $fileHandler = fopen( $this->input->getInput(), "r" );

        while ( $currentFileLine = fgets( $fileHandler ) ) {
            array_push( $this->result, new Row( [ $this->column => trim( $currentFileLine ) ] ) );
        }

        fclose( $fileHandler );

        return $this->result;
    }
}
