<?php declare( strict_types=1 );

namespace Coco\SourceWatcher\Tests\Core\Database\Connections;

use Coco\SourceWatcher\Core\Database\Connections\Connector;
use Coco\SourceWatcher\Core\Database\Connections\SqliteConnector;
use Coco\SourceWatcher\Core\Data\Row;
use Coco\SourceWatcher\Core\Exception\SourceWatcherException;
use Coco\SourceWatcher\Utils\Internationalization;
use Doctrine\DBAL\DBALException;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * Class SqliteConnectorTest
 *
 * @package Coco\SourceWatcher\Tests\Core\Database\Connections
 */
class SqliteConnectorTest extends TestCase
{
    private string $sqliteDbLocation;

    private string $nameIndex;
    private string $emailAddressIndex;

    private string $johnDoeName;
    private string $johnDoeEmailAddress;

    public function setUp () : void
    {
        $this->sqliteDbLocation = __DIR__ . "/../../../../samples/data/sqlite/people-db.sqlite";

        $this->nameIndex = "name";
        $this->emailAddressIndex = "email_address";

        $this->johnDoeName = "John Doe";
        $this->johnDoeEmailAddress = "johndoe@email.com";
    }

    public function testSetGetPath () : void
    {
        $connector = new SqliteConnector();

        $givenPath = $this->sqliteDbLocation;
        $expectedPath = $this->sqliteDbLocation;

        $connector->setPath( $givenPath );

        $this->assertEquals( $expectedPath, $connector->getPath() );
    }

    public function testSetIsMemory () : void
    {
        $connector = new SqliteConnector();

        $givenMemoryValue = false;
        $expectedMemoryValue = false;

        $connector->setMemory( $givenMemoryValue );

        $this->assertEquals( $expectedMemoryValue, $connector->isMemory() );
    }

    /**
     * @throws Exception
     */
    public function testGetConnection () : void
    {
        $connector = new SqliteConnector();
        $connector->setPath( $this->sqliteDbLocation );
        $connector->setMemory( false );

        $this->assertNotNull( $connector->getNewConnection() );
    }

    /**
     * @throws SourceWatcherException
     */
    public function testInsertWithNoTableSpecified () : void
    {
        $this->expectException( SourceWatcherException::class );
        $this->expectExceptionMessage( Internationalization::getInstance()->getText( Connector::class,
            "No_Table_Name_Found" ) );

        $connector = new SqliteConnector();
        $connector->setPath( $this->sqliteDbLocation );
        $connector->setMemory( false );

        $row = new Row( [
            $this->nameIndex => $this->johnDoeName,
            $this->emailAddressIndex => $this->johnDoeEmailAddress
        ] );

        $connector->insert( $row );
    }

    /**
     * When a non-existing table name is given, the connector now creates the table
     * automatically and performs the insert instead of throwing.
     *
     * @throws SourceWatcherException
     */
    public function testInsertUsingWrongConnectionParameters () : void
    {
        $connector = new SqliteConnector();
        $connector->setPath( $this->sqliteDbLocation );
        $connector->setMemory( false );

        $connector->setTableName( "some_non_existing_table" );

        $row = new Row( [
            $this->nameIndex => $this->johnDoeName,
            $this->emailAddressIndex => $this->johnDoeEmailAddress
        ] );

        $this->assertEquals( 1, $connector->insert( $row ) );
    }

    /**
     * @throws SourceWatcherException
     */
    public function testInsertUsingCorrectConnectionParameters () : void
    {
        $connector = new SqliteConnector();
        $connector->setPath( $this->sqliteDbLocation );
        $connector->setMemory( false );

        $connector->setTableName( "people" );

        $row = new Row( [
            $this->nameIndex => $this->johnDoeName,
            $this->emailAddressIndex => $this->johnDoeEmailAddress
        ] );

        $this->assertEquals( 1, $connector->insert( $row ) );
    }

    /**
     * getConnection() returns the connection after a successful insert
     *
     * @throws SourceWatcherException
     */
    public function testGetConnectionAfterInsert () : void
    {
        $connector = new SqliteConnector();
        $connector->setPath( $this->sqliteDbLocation );
        $connector->setMemory( false );
        $connector->setTableName( "people" );

        $row = new Row( [
            $this->nameIndex => $this->johnDoeName,
            $this->emailAddressIndex => $this->johnDoeEmailAddress
        ] );

        $connector->insert( $row );
        $this->assertNotNull( $connector->getConnection() );
    }
}
