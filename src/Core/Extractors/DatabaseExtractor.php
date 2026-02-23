<?php

namespace Coco\SourceWatcher\Core\Extractors;

use Coco\SourceWatcher\Core\Data\Row;
use Coco\SourceWatcher\Core\Exception\SourceWatcherException;
use Coco\SourceWatcher\Core\IO\Inputs\DatabaseInput;
use Coco\SourceWatcher\Core\Step\Extractor;

/**
 * Class DatabaseExtractor
 *
 * @package Coco\SourceWatcher\Core\Extractors
 */
class DatabaseExtractor extends Extractor
{
    protected string $query;

    protected array $availableOptions = [ "query" ];

    public function __construct ()
    {
        $this->query = "";
    }

    public function getQuery () : string
    {
        return $this->query;
    }

    public function setQuery ( string $query ) : void
    {
        $this->query = $query;
    }

    /**
     * @return array
     * @throws SourceWatcherException
     */
    public function extract () : array
    {
        if ( $this->input == null ) {
            throw new SourceWatcherException( "An input must be provided" );
        }

        if ( !( $this->input instanceof DatabaseInput ) ) {
            throw new SourceWatcherException( sprintf( "The input must be an instance of %s", DatabaseInput::class ) );
        }

        if ( $this->input->getInput() == null ) {
            throw new SourceWatcherException( "No database connector found. Set a connector before trying to extract from the database" );
        }

        if ( $this->query == null ) {
            throw new SourceWatcherException( "Query missing" );
        }

        $this->result = [];

        $arrayResults = $this->input->getInput()->executePlainQuery( $this->query );

        foreach ( $arrayResults as $currentRecord ) {
            array_push( $this->result, new Row( $currentRecord ) );
        }

        return $this->result;
    }

    public function getArrayRepresentation () : array
    {
        $result = parent::getArrayRepresentation();

        $dbInput = $this->getInput();
        $dbConnector = $dbInput->getInput();

        $result["input"] = [
            "class" => get_class( $dbConnector ),
            "parameters" => $dbConnector->getConnectionParameters()
        ];

        return $result;
    }
}
