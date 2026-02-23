<?php declare( strict_types=1 );

namespace Coco\SourceWatcher\Tests\Core\Database\Connections;

use Coco\SourceWatcher\Core\Database\Connections\Connector;
use Coco\SourceWatcher\Core\Database\Connections\MySqlConnector;
use Coco\SourceWatcher\Core\Data\Row;
use Coco\SourceWatcher\Core\Exception\SourceWatcherException;
use Coco\SourceWatcher\Tests\Common\ParentTest;
use Coco\SourceWatcher\Utils\Internationalization;
use Exception;

/**
 * Class MySqlConnectorTest
 *
 * @package Coco\SourceWatcher\Tests\Core\Database\Connections
 */
class MySqlConnectorTest extends ParentTest
{
    public string $user;
    public string $password;
    public string $host;
    public int $port;
    public string $dbname;
    public string $unix_socket;
    public string $charset;

    public string $tableName;

    public MySqlConnector $mysqlConnector;

    public Row $row;

    public function setUp () : void
    {
        $this->user = "admin";
        $this->password = "secret";
        $this->host = "localhost";
        $this->port = 3306;
        $this->dbname = "people";
        $this->unix_socket = "/var/run/mysqld/mysqld.sock";
        $this->charset = "utf8";

        $this->tableName = "people";

        $this->mysqlConnector = new MySqlConnector();
        $this->mysqlConnector->setUser( $this->user );
        $this->mysqlConnector->setPassword( $this->password );
        $this->mysqlConnector->setHost( $this->host );
        $this->mysqlConnector->setPort( $this->port );
        $this->mysqlConnector->setDbName( $this->dbname );
        $this->mysqlConnector->setUnixSocket( $this->unix_socket );
        $this->mysqlConnector->setCharset( $this->charset );

        $this->row = new Row( [ "name" => "John Doe", "email_address" => "johndoe@email.com" ] );
    }

    public function testSetGetUnixSocket () : void
    {
        $connector = new MySqlConnector();

        $given = $this->unix_socket;
        $expected = $this->unix_socket;

        $connector->setUnixSocket( $given );

        $this->assertEquals( $expected, $connector->getUnixSocket() );
    }

    public function testSetGetCharset () : void
    {
        $connector = new MySqlConnector();

        $given = $this->charset;
        $expected = $this->charset;

        $connector->setCharset( $given );

        $this->assertEquals( $expected, $connector->getCharset() );
    }

    /**
     * @throws Exception
     */
    public function testGetConnection () : void
    {
        $this->assertNotNull( $this->mysqlConnector->getNewConnection() );
    }

    public function testSetGetConnectionParameters () : void
    {
        $connectionParameters = $this->mysqlConnector->getConnectionParameters();
        $this->assertNotNull( $connectionParameters );

        $connectionParametersKeys = [
            "driver",
            "user",
            "password",
            "host",
            "port",
            "dbname",
            "unix_socket",
            "charset"
        ];

        foreach ( $connectionParametersKeys as $key ) {
            $this->assertArrayHasKey( $key, $connectionParameters );
            $this->assertNotNull( $connectionParameters[$key] );
        }
    }

    /**
     * @throws SourceWatcherException
     */
    public function testInsertWithNoTableSpecified () : void
    {
        $this->expectException( SourceWatcherException::class );
        $this->expectExceptionMessage( Internationalization::getInstance()->getText( Connector::class,
            "No_Table_Name_Found" ) );

        $this->mysqlConnector->insert( $this->row );
    }

    /**
     * @throws SourceWatcherException
     */
    public function testInsertUsingWrongConnectionParameters () : void
    {
        $this->expectException( SourceWatcherException::class );
        $this->expectExceptionMessage( Internationalization::getInstance()->getText( Connector::class,
            "Unexpected_Error" ) );

        $this->mysqlConnector->setTableName( $this->tableName );

        $this->mysqlConnector->insert( $this->row );
    }

    /**
     * @throws SourceWatcherException
     * @group integration
     */
    public function testInsertUsingEnvironmentVariables () : void
    {
        $connector = new MySqlConnector();
        $connector->setUser( $this->getEnvironmentVariable( "UNIT_TEST_MYSQL_USERNAME", null ) );
        $connector->setPassword( $this->getEnvironmentVariable( "UNIT_TEST_MYSQL_PASSWORD", null ) );
        $connector->setHost( $this->getEnvironmentVariable( "UNIT_TEST_MYSQL_HOST", null ) );
        $connector->setPort( $this->getEnvironmentVariable( "UNIT_TEST_MYSQL_PORT", 5432, "intval" ) );
        $connector->setDbName( $this->getEnvironmentVariable( "UNIT_TEST_MYSQL_DATABASE", null ) );

        $connector->setTableName( $this->tableName );

        $this->assertEquals( 1, $connector->insert( $this->row ) );
    }
}
