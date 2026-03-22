<?php

namespace Coco\SourceWatcher\Core\Extractors;

use Coco\SourceWatcher\Core\Data\Row;
use Coco\SourceWatcher\Core\Exception\SourceWatcherException;
use Coco\SourceWatcher\Core\IO\Inputs\FileInput;
use Coco\SourceWatcher\Core\Step\Extractor;

class TesseractOcrExtractor extends Extractor
{
    protected string $column;

    protected string $language;

    protected array $availableOptions = [ "column", "language" ];

    public function __construct ()
    {
        $this->column = "text";
        $this->language = "eng";
    }

    public function getColumn () : string
    {
        return $this->column;
    }

    public function setColumn ( string $column ) : void
    {
        $this->column = $column;
    }

    public function getLanguage () : string
    {
        return $this->language;
    }

    public function setLanguage ( string $language ) : void
    {
        $this->language = $language;
    }

    /**
     * @return array
     * @throws SourceWatcherException
     */
    public function extract () : array
    {
        if ( $this->input === null ) {
            throw new SourceWatcherException( "An input must be provided." );
        }

        if ( !( $this->input instanceof FileInput ) ) {
            throw new SourceWatcherException( sprintf( "The input must be an instance of %s", FileInput::class ) );
        }

        $filePath = $this->input->getInput();

        $language = $this->language !== '' ? $this->language : 'eng';
        $command = 'tesseract ' . escapeshellarg( $filePath ) . ' stdout -l ' . escapeshellarg( $language ) . ' 2>/dev/null';

        exec( $command, $outputLines, $returnCode );

        if ( $returnCode !== 0 ) {
            throw new SourceWatcherException(
                sprintf( 'Tesseract failed (exit code %d). Ensure tesseract is installed and the file is a supported image format (PNG, JPEG, TIFF, etc.).', $returnCode )
            );
        }

        $this->result = [];

        foreach ( $outputLines as $line ) {
            $line = trim( $line );
            if ( $line !== '' ) {
                $this->result[] = new Row( [ $this->column => $line ] );
            }
        }

        return $this->result;
    }
}
