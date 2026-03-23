<?php declare( strict_types=1 );

namespace Coco\SourceWatcher\Tests\Core\Database\Connections;

use Coco\SourceWatcher\Core\Database\Connections\Connector;
use Coco\SourceWatcher\Core\Database\Connections\MySqlConnector;
use Coco\SourceWatcher\Core\Database\Connections\SqliteConnector;
use Coco\SourceWatcher\Core\Exception\SourceWatcherException;
use Doctrine\DBAL\Connection;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * Class ConnectorTest
 *
 * @package Coco\SourceWatcher\Tests\Core\Database\Connections
 */
class ConnectorTest extends TestCase
{
    /**
     * SqliteConnector that throws a generic Exception from getNewConnection (to cover executePlainQuery's second catch).
     */
    private static function createConnectorThrowingGenericException () : Connector
    {
        return new class extends SqliteConnector {
            public function getNewConnection () : Connection
            {
                throw new Exception( "generic" );
            }
        };
    }

    public function testGetDriver () : void
    {
        $connector = new MySqlConnector();

        $expected = "pdo_mysql";

        $this->assertEquals( $expected, $connector->getDriver() );
    }

    public function testSetGetUser () : void
    {
        $connector = new MySqlConnector();

        $given = "user";
        $expected = "user";

        $connector->setUser( $given );

        $this->assertEquals( $expected, $connector->getUser() );
    }

    public function testSetGetPassword () : void
    {
        $connector = new MySqlConnector();

        $given = "password";
        $expected = "password";

        $connector->setPassword( $given );

        $this->assertEquals( $expected, $connector->getPassword() );
    }

    public function testSetGetTableName () : void
    {
        $connector = new MySqlConnector();

        $given = "people";
        $expected = "people";

        $connector->setTableName( $given );

        $this->assertEquals( $expected, $connector->getTableName() );
    }

    public function testSetGetBulkInsert () : void
    {
        $connector = new MySqlConnector();
        $this->assertFalse( $connector->isBulkInsert() );
        $connector->setBulkInsert( true );
        $this->assertTrue( $connector->isBulkInsert() );
    }

    public function testInsertThrowsWhenTableNameEmpty () : void
    {
        $connector = new MySqlConnector();
        $connector->setUser( "u" );
        $connector->setPassword( "p" );
        $connector->setHost( "h" );
        $connector->setDbName( "d" );
        $connector->setTableName( "" );

        $this->expectException( \Coco\SourceWatcher\Core\Exception\SourceWatcherException::class );
        $this->expectExceptionMessage( "table name" );
        $connector->insert( new \Coco\SourceWatcher\Core\Data\Row( [ "id" => 1 ] ) );
    }

    /**
     * executePlainQuery success path using in-memory SQLite
     *
     * @throws SourceWatcherException
     */
    public function testExecutePlainQuerySuccess () : void
    {
        $connector = new SqliteConnector();
        $connector->setPath( ":memory:" );
        $connector->setMemory( true );

        $rows = $connector->executePlainQuery( "SELECT 1 AS one" );
        $this->assertIsArray( $rows );
        $this->assertCount( 1, $rows );
        $this->assertSame( 1, (int) $rows[0]["one"] );
    }

    /**
     * executePlainQuery throws on invalid SQL (covers catch branches)
     */
    public function testExecutePlainQueryThrowsOnInvalidSql () : void
    {
        $connector = new SqliteConnector();
        $connector->setPath( ":memory:" );
        $connector->setMemory( true );

        $this->expectException( SourceWatcherException::class );
        $this->expectExceptionMessage( "Something went wrong" );
        $connector->executePlainQuery( "INVALID SQL SYNTAX {{" );
    }

    /**
     * executePlainQuery throws when getNewConnection throws generic Exception (covers second catch block)
     */
    public function testExecutePlainQueryThrowsOnGenericException () : void
    {
        $connector = self::createConnectorThrowingGenericException();

        $this->expectException( SourceWatcherException::class );
        $this->expectExceptionMessage( "Something unexpected went wrong" );
        $connector->executePlainQuery( "SELECT 1" );
    }

    /**
     * ensureTableExists returns immediately when the column names list is empty.
     * Tested via reflection so the early-return guard is exercised without
     * triggering a subsequent insert failure.
     * Covers: the `return;` on line 120 of Connector.
     *
     * @throws \Exception
     */
    public function testEnsureTableExistsIsNoopWhenColumnNamesIsEmpty () : void
    {
        $connector = new SqliteConnector();
        $connector->setPath( ":memory:" );
        $connector->setMemory( true );

        $connection = $connector->getNewConnection();

        $method = ( new \ReflectionClass( $connector ) )->getMethod( 'ensureTableExists' );
        $method->invoke( $connector, $connection, [] );

        $connection->close();

        $this->addToAssertionCount( 1 );
    }

    /**
     * Inserting a row whose column value is an array causes normalizeRowAttributesForInsert
     * to JSON-encode it before the value reaches Doctrine/PDO.
     * Covers: the json_encode lines in normalizeRowAttributesForInsert.
     *
     * @throws SourceWatcherException
     */
    public function testInsertRowWithArrayValueJsonEncodesTheColumn () : void
    {
        $connector = new SqliteConnector();
        $connector->setPath( ":memory:" );
        $connector->setMemory( true );
        $connector->setTableName( "test_table" );

        $affected = $connector->insert( new \Coco\SourceWatcher\Core\Data\Row( [ "tags" => [ "php", "etl" ] ] ) );

        $this->assertSame( 1, $affected );
    }
}
