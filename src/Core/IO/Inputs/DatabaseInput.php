<?php

namespace Coco\SourceWatcher\Core\IO\Inputs;

use Coco\SourceWatcher\Core\Database\Connections\Connector;

/**
 * Class DatabaseInput
 *
 * @package Coco\SourceWatcher\Core\IO\Inputs
 */
class DatabaseInput extends Input
{
    private ?Connector $databaseConnector;

    public function __construct ( ?Connector $databaseConnector = null )
    {
        $this->databaseConnector = $databaseConnector;
    }

    public function getInput () : ?Connector
    {
        return $this->databaseConnector;
    }

    /**
     * @param Connector|null $input
     */
    public function setInput ( $input ) : void
    {
        $this->databaseConnector = $input instanceof Connector ? $input : null;
    }
}
