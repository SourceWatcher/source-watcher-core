<?php

namespace Coco\SourceWatcher\Core\Database\Connections;

/**
 * Class SqliteConnector
 *
 * @package Coco\SourceWatcher\Core\Database\Connections
 *
 * Following the definition from:
 *     https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#pdo-sqlite
 */
class SqliteConnector extends EmbeddedDatabaseConnector
{
    protected string $path;

    protected bool $memory;

    public function __construct ()
    {
        parent::__construct();

        $this->driver = "pdo_sqlite";

        $this->memory = false;
    }

    public function getPath () : string
    {
        return $this->path;
    }

    public function setPath ( string $path ) : void
    {
        $this->path = $path;
    }

    public function isMemory () : bool
    {
        return $this->memory;
    }

    public function setMemory ( bool $memory ) : void
    {
        $this->memory = $memory;
    }

    public function getConnectionParameters () : array
    {
        $this->connectionParameters = [
            "driver" => $this->driver,
            "user" => $this->user,
            "password" => $this->password
        ];

        if ( isset( $this->path ) && $this->path !== "" ) {
            $this->connectionParameters["path"] = $this->path;
        }

        if ( isset( $this->tableName ) && $this->tableName !== "" ) {
            $this->connectionParameters["tableName"] = $this->tableName;
        }

        $this->connectionParameters["memory"] = $this->memory;

        return $this->connectionParameters;
    }
}
