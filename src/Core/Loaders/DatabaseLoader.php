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
     * Normalize row attributes so arrays/objects become JSON strings (avoids "Array" in DB).
     *
     * @param array<string, mixed> $attributes
     * @return array<string, string|int|float|bool|null>
     */
    private function normalizeRowAttributes ( array $attributes ) : array
    {
        $normalized = [];
        foreach ( $attributes as $key => $value ) {
            if ( $value === null || is_scalar( $value ) ) {
                $normalized[$key] = $value;
            } else {
                $encoded = json_encode( $value, JSON_UNESCAPED_UNICODE );
                $normalized[$key] = ( $encoded !== false ) ? $encoded : 'null';
            }
        }
        return $normalized;
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

        $normalized = new Row( $this->normalizeRowAttributes( $row->getAttributes() ) );

        foreach ( $output as $currentConnector ) {
            if ( $currentConnector != null ) {
                $currentConnector->insert( $normalized );
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
