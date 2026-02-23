<?php

namespace Coco\SourceWatcher\Core\IO\Outputs;

use Coco\SourceWatcher\Core\Database\Connections\Connector;

/**
 * Class DatabaseOutput
 *
 * @package Coco\SourceWatcher\Core\IO\Outputs
 */
class DatabaseOutput extends Output
{
    private ?Connector $databaseConnector;

    private array $extraConnectors = [];

    public function __construct ( ?Connector $databaseConnector = null, Connector ...$extraConnectors )
    {
        $this->databaseConnector = $databaseConnector;

        if ( $extraConnectors != null ) {
            foreach ( $extraConnectors as $currentConnector ) {
                $this->extraConnectors[] = $currentConnector;
            }
        }
    }

    public function getOutput () : array
    {
        return array_merge( [ $this->databaseConnector ], $this->extraConnectors );
    }

    public function setOutput ( $output )
    {
        $this->databaseConnector = $output;
    }
}
