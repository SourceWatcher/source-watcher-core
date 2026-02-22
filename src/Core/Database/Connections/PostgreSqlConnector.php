<?php

namespace Coco\SourceWatcher\Core\Database\Connections;

use Doctrine\DBAL\Connection;

/**
 * Class PostgreSqlConnector
 *
 * @package Coco\SourceWatcher\Core\Database\Connections
 *
 * Following the definition from:
 *     https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#pdo-pgsql
 */
class PostgreSqlConnector extends ClientServerDatabaseConnector
{
    protected string $charset = "";

    protected string $defaultDatabaseName = "";

    protected string $sslMode = "";

    protected string $sslRootCert = "";

    protected string $sslCert = "";

    protected string $sslKey = "";

    protected string $sslCrl = "";

    protected string $applicationName = "";

    public function __construct ()
    {
        parent::__construct();

        $this->driver = "pdo_pgsql";

        $this->port = 5432;
    }

    public function getCharset () : string
    {
        return $this->charset;
    }

    public function setCharset ( string $charset ) : void
    {
        $this->charset = $charset;
    }

    public function getDefaultDatabaseName () : string
    {
        return $this->defaultDatabaseName;
    }

    public function setDefaultDatabaseName ( ?string $defaultDatabaseName ) : void
    {
        $this->defaultDatabaseName = $defaultDatabaseName ?? "";
    }

    public function getSslMode () : string
    {
        return $this->sslMode;
    }

    public function setSslMode ( string $sslMode ) : void
    {
        $this->sslMode = $sslMode;
    }

    public function getSslRootCert () : string
    {
        return $this->sslRootCert;
    }

    public function setSslRootCert ( string $sslRootCert ) : void
    {
        $this->sslRootCert = $sslRootCert;
    }

    public function getSslCert () : string
    {
        return $this->sslCert;
    }

    public function setSslCert ( string $sslCert ) : void
    {
        $this->sslCert = $sslCert;
    }

    public function getSslKey () : string
    {
        return $this->sslKey;
    }

    public function setSslKey ( string $sslKey ) : void
    {
        $this->sslKey = $sslKey;
    }

    public function getSslCrl () : string
    {
        return $this->sslCrl;
    }

    public function setSslCrl ( string $sslCrl ) : void
    {
        $this->sslCrl = $sslCrl;
    }

    public function getApplicationName () : string
    {
        return $this->applicationName;
    }

    public function setApplicationName ( string $applicationName ) : void
    {
        $this->applicationName = $applicationName;
    }

    public function getConnectionParameters () : array
    {
        $this->extraParameters = [
            "charset" => $this->charset,
            "default_dbname" => $this->defaultDatabaseName,
            "sslmode" => $this->sslMode,
            "sslrootcert" => $this->sslRootCert,
            "sslcert" => $this->sslCert,
            "sslkey" => $this->sslKey,
            "sslcrl" => $this->sslCrl,
            "application_name" => $this->applicationName
        ];

        return parent::getConnectionParameters();
    }

    protected function executeExtraStatements ( Connection $connection ) : void
    {
        // TODO: Implement executeExtraStatements() method.
    }
}
