<?php declare( strict_types=1 );

namespace Coco\SourceWatcher\Tests\Core\Extractors;

use Coco\SourceWatcher\Core\Database\Connections\MySqlConnector;
use Coco\SourceWatcher\Core\Database\Connections\SqliteConnector;
use Coco\SourceWatcher\Core\Extractors\DatabaseExtractor;
use Coco\SourceWatcher\Core\IO\Inputs\DatabaseInput;
use Coco\SourceWatcher\Core\IO\Inputs\FileInput;
use Coco\SourceWatcher\Core\Exception\SourceWatcherException;
use Coco\SourceWatcher\Tests\Common\ParentTest;

/**
 * Class DatabaseExtractorTest
 *
 * @package Coco\SourceWatcher\Tests\Core\Extractors
 */
class DatabaseExtractorTest extends ParentTest
{
    private string $tableName;
    private MySqlConnector $mysqlConnector;

    public function setUp () : void
    {
        $this->tableName = "people";

        $this->mysqlConnector = new MySqlConnector();
        $this->mysqlConnector->setUser( $this->getEnvironmentVariable( "UNIT_TEST_MYSQL_USERNAME", null ) );
        $this->mysqlConnector->setPassword( $this->getEnvironmentVariable( "UNIT_TEST_MYSQL_PASSWORD", null ) );
        $this->mysqlConnector->setHost( $this->getEnvironmentVariable( "UNIT_TEST_MYSQL_HOST", null ) );
        $this->mysqlConnector->setPort( $this->getEnvironmentVariable( "UNIT_TEST_MYSQL_PORT", 5432, "intval" ) );
        $this->mysqlConnector->setDbName( $this->getEnvironmentVariable( "UNIT_TEST_MYSQL_DATABASE", null ) );

        $this->mysqlConnector->setTableName( $this->tableName );
    }

    public function testSetGetQuery () : void
    {
        $extractor = new DatabaseExtractor();
        $this->assertSame( "", $extractor->getQuery() );
        $extractor->setQuery( "SELECT * FROM t" );
        $this->assertSame( "SELECT * FROM t", $extractor->getQuery() );
    }

    public function testExtractThrowsWhenNoInput () : void
    {
        $this->expectException( SourceWatcherException::class );
        $this->expectExceptionMessage( "An input must be provided" );

        $extractor = new DatabaseExtractor();
        $extractor->setQuery( "SELECT 1" );
        $extractor->extract();
    }

    public function testExtractThrowsWhenNotDatabaseInput () : void
    {
        $this->expectException( SourceWatcherException::class );
        $this->expectExceptionMessage( DatabaseInput::class );

        $extractor = new DatabaseExtractor();
        $extractor->setInput( new FileInput( "/tmp/any" ) );
        $extractor->setQuery( "SELECT 1" );
        $extractor->extract();
    }

    public function testExtractThrowsWhenNoConnector () : void
    {
        $this->expectException( SourceWatcherException::class );
        $this->expectExceptionMessage( "No database connector found" );

        $extractor = new DatabaseExtractor();
        $input = new DatabaseInput();
        $extractor->setInput( $input );
        $extractor->setQuery( "SELECT 1" );
        $extractor->extract();
    }

    public function testExtractThrowsWhenQueryMissing () : void
    {
        $this->expectException( SourceWatcherException::class );
        $this->expectExceptionMessage( "Query missing" );

        $connector = new SqliteConnector();
        $connector->setPath( ":memory:" );
        $connector->setMemory( true );
        $extractor = new DatabaseExtractor();
        $extractor->setInput( new DatabaseInput( $connector ) );
        $extractor->setQuery( "" );
        $extractor->extract();
    }

    /**
     * extract() success path with in-memory SQLite (covers executePlainQuery and foreach)
     *
     * @throws SourceWatcherException
     */
    public function testExtractWithSqlite () : void
    {
        $connector = new SqliteConnector();
        $connector->setPath( ":memory:" );
        $connector->setMemory( true );

        $extractor = new DatabaseExtractor();
        $extractor->setInput( new DatabaseInput( $connector ) );
        $extractor->setQuery( "SELECT 1 AS num, 'hello' AS msg" );

        $result = $extractor->extract();

        $this->assertCount( 1, $result );
        $this->assertEquals( 1, $result[0]["num"] );
        $this->assertSame( "hello", $result[0]["msg"] );
    }

    /**
     * getArrayRepresentation includes connector class and connection parameters
     *
     * @throws SourceWatcherException
     */
    public function testGetArrayRepresentation () : void
    {
        $connector = new SqliteConnector();
        $connector->setPath( ":memory:" );
        $connector->setMemory( true );
        $extractor = new DatabaseExtractor();
        $extractor->setInput( new DatabaseInput( $connector ) );
        $extractor->setQuery( "SELECT 1" );

        $arr = $extractor->getArrayRepresentation();

        $this->assertArrayHasKey( "input", $arr );
        $this->assertSame( SqliteConnector::class, $arr["input"]["class"] );
        $this->assertArrayHasKey( "parameters", $arr["input"] );
    }

    /**
     * @group integration
     * @throws SourceWatcherException
     */
    public function testExtractFromMySqlTable () : void
    {
        $query = "SELECT * FROM " . $this->tableName;

        $databaseExtractor = new DatabaseExtractor();
        $databaseExtractor->setInput( new DatabaseInput( $this->mysqlConnector ) );
        $databaseExtractor->setQuery( $query );

        $result = $databaseExtractor->extract();

        $this->assertNotEmpty( $result );
    }
}
