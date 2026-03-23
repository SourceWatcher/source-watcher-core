<?php declare( strict_types=1 );

namespace Coco\SourceWatcher\Tests\Core\Loaders;

use Coco\SourceWatcher\Core\Database\Connections\Connector;
use Coco\SourceWatcher\Core\IO\Outputs\DatabaseOutput;
use Coco\SourceWatcher\Core\IO\Outputs\Output;
use Coco\SourceWatcher\Core\Loaders\DatabaseLoader;
use Coco\SourceWatcher\Core\Data\Row;
use Coco\SourceWatcher\Core\Exception\SourceWatcherException;
use PHPUnit\Framework\TestCase;

/**
 * Class DatabaseLoaderTest
 *
 * @package Coco\SourceWatcher\Tests\Core\Loaders
 */
class DatabaseLoaderTest extends TestCase
{
    private string $janeDoe;

    public function setUp () : void
    {
        $this->janeDoe = "Jane Doe";
    }

    /**
     * This unit test is testing the getOutput and setOutput methods of the Loader abstract class.
     */
    public function testSetAndGetOutput () : void
    {
        $databaseOutput = new DatabaseOutput( $this->createMock( Connector::class ) );

        $databaseLoader = new DatabaseLoader();
        $databaseLoader->setOutput( $databaseOutput );

        $this->assertSame( $databaseOutput, $databaseLoader->getOutput() );
    }

    /**
     * @throws SourceWatcherException
     */
    public function testInsertRowWithNoOutput () : void
    {
        $this->expectException( SourceWatcherException::class );

        $databaseLoader = new DatabaseLoader();
        $databaseLoader->load( new Row( [ "name" => $this->janeDoe ] ) );
    }

    /**
     * @throws SourceWatcherException
     */
    public function testInsertRowWithNonDatabaseOutput () : void
    {
        $this->expectException( SourceWatcherException::class );

        $databaseLoader = new DatabaseLoader();
        $databaseLoader->setOutput( $this->createMock( Output::class ) );
        $databaseLoader->load( new Row( [ "name" => $this->janeDoe ] ) );
    }

    /**
     * @throws SourceWatcherException
     */
    public function testInsertRowWithDatabaseOutputWithoutConnector () : void
    {
        $this->expectException( SourceWatcherException::class );

        $databaseLoader = new DatabaseLoader();
        $databaseLoader->setOutput( new DatabaseOutput() );
        $databaseLoader->load( new Row( [ "name" => $this->janeDoe ] ) );
    }

    /**
     * @throws SourceWatcherException
     */
    public function testInsertRowWithDatabaseOutputWithMockConnector () : void
    {
        $databaseLoader = new DatabaseLoader();
        $databaseLoader->setOutput( new DatabaseOutput( $this->createMock( Connector::class ) ) );

        $this->assertNull( $databaseLoader->load( new Row( [ "name" => $this->janeDoe ] ) ) );
    }

    /**
     * A row whose column value is an array (non-scalar) must be JSON-encoded
     * before insertion rather than cast to the string "Array".
     * Covers the normalizeRowAttributes non-scalar branch (json_encode lines).
     *
     * @throws SourceWatcherException
     */
    public function testInsertRowWithArrayValueJsonEncodesTheColumn () : void
    {
        $databaseLoader = new DatabaseLoader();
        $databaseLoader->setOutput( new DatabaseOutput( $this->createMock( Connector::class ) ) );

        $this->assertNull(
            $databaseLoader->load( new Row( [ "tags" => [ "php", "etl", "ocr" ] ] ) )
        );
    }
}
