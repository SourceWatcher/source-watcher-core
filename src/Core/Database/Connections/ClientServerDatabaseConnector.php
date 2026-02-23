<?php

namespace Coco\SourceWatcher\Core\Database\Connections;

/**
 * Class ClientServerDatabaseConnector
 *
 * @package Coco\SourceWatcher\Core\Database\Connections
 */
abstract class ClientServerDatabaseConnector extends Connector
{
    protected string $host;

    protected int $port;

    protected string $dbName;

    public function getHost () : string
    {
        return $this->host;
    }

    public function setHost ( ?string $host ) : void
    {
        $this->host = $host ?? "";
    }

    public function getPort () : int
    {
        return $this->port;
    }

    public function setPort ( int $port ) : void
    {
        $this->port = $port;
    }

    public function getDbName () : string
    {
        return $this->dbName;
    }

    public function setDbName ( ?string $dbName ) : void
    {
        $this->dbName = $dbName ?? "";
    }

    protected array $extraParameters;

    public function getConnectionParameters () : array
    {
        $this->connectionParameters = [];

        $this->connectionParameters["driver"] = $this->driver;
        $this->connectionParameters["user"] = $this->user;
        $this->connectionParameters["password"] = $this->password;
        $this->connectionParameters["host"] = $this->host;
        $this->connectionParameters["port"] = $this->port;
        $this->connectionParameters["dbname"] = $this->dbName;

        foreach ( $this->extraParameters as $parameterName => $localVariable ) {
            if ( isset( $localVariable ) && $localVariable !== "" ) {
                $this->connectionParameters[$parameterName] = $localVariable;
            }
        }

        return $this->connectionParameters;
    }
}
