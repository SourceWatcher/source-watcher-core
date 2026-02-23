<?php declare( strict_types=1 );

namespace Coco\SourceWatcher\Tests\Core\Database\Connections;

use Coco\SourceWatcher\Core\Database\Connections\PostgreSqlConnector;
use Coco\SourceWatcher\Core\Data\Row;
use Coco\SourceWatcher\Core\Exception\SourceWatcherException;
use Coco\SourceWatcher\Tests\Common\ParentTest;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Exception;
use ReflectionMethod;

/**
 * Class PostgreSqlConnectorTest
 *
 * @package Coco\SourceWatcher\Tests\Core\Database\Connections
 */
class PostgreSqlConnectorTest extends ParentTest
{
    private string $databaseName;

    private string $sslAllowMode;
    private string $sslRootCert;
    private string $sslCert;
    private string $sslKey;
    private string $sslCrl;

    private string $appName;

    public function setUp () : void
    {
        $this->databaseName = "people";

        $this->sslAllowMode = "allow";
        $this->sslRootCert = "~/.postgresql/root.crt";
        $this->sslCert = "~/.postgresql/postgresql.crt";
        $this->sslKey = "~/.postgresql/postgresql.key";
        $this->sslCrl = "~/.postgresql/root.crl";

        $this->appName = "App Name for PG Stat Activity";
    }

    public function testSetGetCharset () : void
    {
        $connector = new PostgreSqlConnector();

        $givenCharset = "utf8";
        $expectedCharset = "utf8";

        $connector->setCharset( $givenCharset );

        $this->assertEquals( $expectedCharset, $connector->getCharset() );
    }

    public function testSetGetDefaultDatabaseName () : void
    {
        $connector = new PostgreSqlConnector();

        $givenDefaultDatabaseName = $this->databaseName;
        $expectedDefaultDatabaseName = $this->databaseName;

        $connector->setDefaultDatabaseName( $givenDefaultDatabaseName );

        $this->assertEquals( $expectedDefaultDatabaseName, $connector->getDefaultDatabaseName() );
    }

    public function testSetDefaultDatabaseNameWithNull () : void
    {
        $connector = new PostgreSqlConnector();
        $connector->setDefaultDatabaseName( null );
        $this->assertSame( "", $connector->getDefaultDatabaseName() );
    }

    public function testSetGetSslMode () : void
    {
        $connector = new PostgreSqlConnector();

        $givenSslMode = $this->sslAllowMode;
        $expectedSslMode = $this->sslAllowMode;

        $connector->setSslMode( $givenSslMode );

        $this->assertEquals( $expectedSslMode, $connector->getSslMode() );
    }

    public function testSetGetSslRootCert () : void
    {
        $connector = new PostgreSqlConnector();

        $givenSslRootCert = $this->sslRootCert;
        $expectedSslRootCert = $this->sslRootCert;

        $connector->setSslRootCert( $givenSslRootCert );

        $this->assertEquals( $expectedSslRootCert, $connector->getSslRootCert() );
    }

    public function testSetGetSslCert () : void
    {
        $connector = new PostgreSqlConnector();

        $givenSslCert = $this->sslCert;
        $expectedSslCert = $this->sslCert;

        $connector->setSslCert( $givenSslCert );

        $this->assertEquals( $expectedSslCert, $connector->getSslCert() );
    }

    public function testSetGetSslKey () : void
    {
        $connector = new PostgreSqlConnector();

        $givenSslKey = $this->sslKey;
        $expectedSslKey = $this->sslKey;

        $connector->setSslKey( $givenSslKey );

        $this->assertEquals( $expectedSslKey, $connector->getSslKey() );
    }

    public function testSetGetSslCrl () : void
    {
        $connector = new PostgreSqlConnector();

        $givenSslCrl = $this->sslCrl;
        $expectedSslCrl = $this->sslCrl;

        $connector->setSslCrl( $givenSslCrl );

        $this->assertEquals( $expectedSslCrl, $connector->getSslCrl() );
    }

    public function testSetGetApplicationName () : void
    {
        $connector = new PostgreSqlConnector();

        $givenAppName = $this->appName;
        $expectedAppName = $this->appName;

        $connector->setApplicationName( $givenAppName );

        $this->assertEquals( $expectedAppName, $connector->getApplicationName() );
    }

    /**
     * getConnectionParameters returns params including Postgres-specific keys
     */
    public function testGetConnectionParameters () : void
    {
        $connector = new PostgreSqlConnector();
        $connector->setUser( "u" );
        $connector->setPassword( "p" );
        $connector->setHost( "localhost" );
        $connector->setPort( 5432 );
        $connector->setDbName( "db" );
        $connector->setCharset( "utf8" );
        $connector->setDefaultDatabaseName( "mydb" );
        $connector->setApplicationName( "test" );

        $params = $connector->getConnectionParameters();

        $this->assertIsArray( $params );
        $this->assertArrayHasKey( "charset", $params );
        $this->assertSame( "utf8", $params["charset"] );
        $this->assertArrayHasKey( "default_dbname", $params );
        $this->assertSame( "mydb", $params["default_dbname"] );
        $this->assertArrayHasKey( "application_name", $params );
        $this->assertSame( "test", $params["application_name"] );
    }

    /**
     * executeExtraStatements is invoked (covers empty implementation)
     */
    public function testExecuteExtraStatements () : void
    {
        $connector = new PostgreSqlConnector();
        $connection = $this->createMock( Connection::class );

        $method = new ReflectionMethod( PostgreSqlConnector::class, "executeExtraStatements" );
        if ( PHP_VERSION_ID < 80100 ) {
            $method->setAccessible( true );
        }
        $method->invoke( $connector, $connection );

        $this->addToAssertionCount( 1 );
    }

    /**
     * @throws Exception
     */
    public function testGetConnection () : void
    {
        $connector = new PostgreSqlConnector();
        $connector->setUser( "admin" );
        $connector->setPassword( "secret" );
        $connector->setHost( "localhost" );
        $connector->setPort( 5432 );
        $connector->setDbName( $this->databaseName );
        $connector->setCharset( "utf8" );
        $connector->setDefaultDatabaseName( $this->databaseName );
        $connector->setSslMode( $this->sslAllowMode );
        $connector->setSslRootCert( $this->sslRootCert );
        $connector->setSslCert( $this->sslCert );
        $connector->setSslKey( $this->sslKey );
        $connector->setSslCrl( $this->sslCrl );
        $connector->setApplicationName( $this->appName );

        $this->assertNotNull( $connector->getNewConnection() );
    }

    /**
     * @throws SourceWatcherException
     * @group integration
     */
    public function testInsertUsingEnvironmentVariables () : void
    {
        $user = $this->getEnvironmentVariable( "UNIT_TEST_POSTGRESQL_USER", null );
        $password = $this->getEnvironmentVariable( "UNIT_TEST_POSTGRESQL_PASSWORD", null );
        $host = $this->getEnvironmentVariable( "UNIT_TEST_POSTGRESQL_HOST", null );
        $port = $this->getEnvironmentVariable( "UNIT_TEST_POSTGRESQL_PORT", 5432, "intval" );
        $dbName = $this->getEnvironmentVariable( "UNIT_TEST_POSTGRESQL_DB_NAME", null );
        $defaultDatabaseName = $this->getEnvironmentVariable( "UNIT_TEST_POSTGRESQL_DEFAULT_DATABASE_NAME", null );

        $connector = new PostgreSqlConnector();
        $connector->setUser( $user );
        $connector->setPassword( $password );
        $connector->setHost( $host );
        $connector->setPort( $port );
        $connector->setDbName( $dbName );
        $connector->setDefaultDatabaseName( $defaultDatabaseName );

        $connector->setTableName( "people" );

        $row = new Row( [ "name" => "John Doe", "email_address" => "johndoe@email.com" ] );

        $this->assertEquals( 1, $connector->insert( $row ) );
    }
}
