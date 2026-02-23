<?php

namespace Coco\SourceWatcher\Core\Loaders;

use Coco\SourceWatcher\Core\Data\Row;
use Coco\SourceWatcher\Core\Exception\SourceWatcherException;
use Coco\SourceWatcher\Core\IO\Outputs\DatabaseOutput;
use Coco\SourceWatcher\Core\Step\Loader;

/**
 * Class DatabaseLoader
 *
 * @package Coco\SourceWatcher\Core\Loaders
 */
class DatabaseLoader extends Loader
{
    /**
     * @param Row $row
     * @throws SourceWatcherException
     */
    public function load ( Row $row )
    {
        $this->insert( $row );
    }

    /**
     * @param Row $row
     * @throws SourceWatcherException
     */
    protected function insert ( Row $row ) : void
    {
        if ( $this->output == null ) {
            throw new SourceWatcherException( "An output must be provided" );
        }

        if ( !( $this->output instanceof DatabaseOutput ) ) {
            throw new SourceWatcherException( sprintf( "The output must be an instance of %s",
                DatabaseOutput::class ) );
        }

        $output = $this->output->getOutput();

        if ( $output == null || empty( $output ) || ( sizeof( $output ) == 1 && $output[0] == null ) ) {
            throw new SourceWatcherException( "No database connector found. Set a connector before trying to insert a row" );
        }

        foreach ( $output as $currentConnector ) {
            if ( $currentConnector != null ) {
                $currentConnector->insert( $row );
            }
        }
    }

    public function getArrayRepresentation () : array
    {
        $result = parent::getArrayRepresentation();

        $result["output"] = [];

        $output = $this->output->getOutput();

        foreach ( $output as $currentConnector ) {
            if ( $currentConnector != null ) {
                $result["output"][] = [
                    "class" => get_class( $currentConnector ),
                    "parameters" => $currentConnector->getConnectionParameters()
                ];
            }
        }

        return $result;
    }
}
