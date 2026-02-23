<?php declare( strict_types=1 );

namespace Coco\SourceWatcher\Tests\Core\Extractors;

use Coco\SourceWatcher\Core\Extractors\CsvExtractor;
use Coco\SourceWatcher\Core\IO\Inputs\FileInput;
use Coco\SourceWatcher\Core\IO\Inputs\Input;
use Coco\SourceWatcher\Core\Row;
use Coco\SourceWatcher\Core\SourceWatcherException;
use PHPUnit\Framework\TestCase;

/**
 * Class CsvExtractorTest
 *
 * @package Coco\SourceWatcher\Tests\Core\Extractors
 */
class CsvExtractorTest extends TestCase
{
    private string $csvLocation;

    private string $idIndex;
    private string $nameIndex;
    private string $emailIndex;
    private string $emailAddressIndex;

    /** @var array<int, array<string, mixed>> */
    private array $allRowsExpected;

    public function setUp () : void
    {
        $this->csvLocation = __DIR__ . "/../../../samples/data/csv/csv1.csv";

        $this->idIndex = "id";
        $this->nameIndex = "name";
        $this->emailIndex = "email";
        $this->emailAddressIndex = "email_address";

        $this->allRowsExpected = [
            [ "id" => 9, "name" => "Avery", "email" => "avery@example.com" ],
            [ "id" => 7, "name" => "Geirþrúður", "email" => "geirthrudur@example.com" ],
            [ "id" => 4, "name" => "Jane", "email" => "jane@example.com" ],
            [ "id" => 3, "name" => "John", "email" => "john@example.com" ],
            [ "id" => 6, "name" => "Maria", "email" => "maria@example.com" ],
            [ "id" => 5, "name" => "Robin", "email" => "robin@example.com" ],
            [ "id" => 8, "name" => "Thomas", "email" => "thomas@example.com" ],
        ];
    }

    public function testSetGetColumns () : void
    {
        $csvExtractor = new CsvExtractor();

        $givenColumns = [ $this->idIndex, $this->nameIndex, $this->emailIndex ];
        $expectedColumns = [ $this->idIndex, $this->nameIndex, $this->emailIndex ];

        $csvExtractor->setColumns( $givenColumns );

        $this->assertEquals( $expectedColumns, $csvExtractor->getColumns() );
    }

    public function testSetGetDelimiter () : void
    {
        $csvExtractor = new CsvExtractor();

        $givenDelimiter = ",";
        $expectedDelimiter = ",";

        $csvExtractor->setDelimiter( $givenDelimiter );

        $this->assertEquals( $expectedDelimiter, $csvExtractor->getDelimiter() );
    }

    public function testSetGetEnclosure () : void
    {
        $csvExtractor = new CsvExtractor();

        $givenEnclosure = "\"";
        $expectedEnclosure = "\"";

        $csvExtractor->setEnclosure( $givenEnclosure );

        $this->assertEquals( $expectedEnclosure, $csvExtractor->getEnclosure() );
    }

    public function testSetGetInput () : void
    {
        $csvExtractor = new CsvExtractor();

        $givenInput = new FileInput( "/some/file/path/file.csv" );
        $expectedInput = new FileInput( "/some/file/path/file.csv" );

        $csvExtractor->setInput( $givenInput );

        $this->assertEquals( $expectedInput, $csvExtractor->getInput() );
    }

    /**
     * @throws SourceWatcherException
     */
    public function testExceptionNoInput () : void
    {
        $this->expectException( SourceWatcherException::class );

        $csvExtractor = new CsvExtractor();
        $csvExtractor->extract();
    }

    /**
     * @throws SourceWatcherException
     */
    public function testExceptionNoFileInput () : void
    {
        $this->expectException( SourceWatcherException::class );

        $csvExtractor = new CsvExtractor();
        $csvExtractor->setInput( $this->createMock( Input::class ) );
        $csvExtractor->extract();
    }

    /**
     * @throws SourceWatcherException
     */
    public function testLoadCsvWithDefaultOptions () : void
    {
        $csvExtractor = new CsvExtractor();

        $expected = array_map( function ( array $row ) {
            return new Row( [
                $this->idIndex => $row["id"],
                $this->nameIndex => $row["name"],
                $this->emailIndex => $row["email"]
            ] );
        }, $this->allRowsExpected );

        $csvExtractor->setInput( new FileInput( $this->csvLocation ) );

        $this->assertEquals( $expected, $csvExtractor->extract() );
    }

    /**
     * @throws SourceWatcherException
     */
    public function testColumnsWithNoIndex1 () : void
    {
        $csvExtractor = new CsvExtractor();
        $csvExtractor->setColumns( [ $this->idIndex, $this->emailIndex ] );
        $csvExtractor->setInput( new FileInput( $this->csvLocation ) );

        $expected = array_map( function ( array $row ) {
            return new Row( [ $this->idIndex => $row["id"], $this->emailIndex => $row["email"] ] );
        }, $this->allRowsExpected );

        $this->assertEquals( $expected, $csvExtractor->extract() );
    }

    /**
     * @throws SourceWatcherException
     */
    public function testColumnsWithNoIndex2 () : void
    {
        $csvExtractor = new CsvExtractor();
        $csvExtractor->setColumns( [ $this->idIndex, $this->nameIndex, $this->emailIndex ] );
        $csvExtractor->setInput( new FileInput( $this->csvLocation ) );

        $expected = array_map( function ( array $row ) {
            return new Row( [
                $this->idIndex => $row["id"],
                $this->nameIndex => $row["name"],
                $this->emailIndex => $row["email"]
            ] );
        }, $this->allRowsExpected );

        $this->assertEquals( $expected, $csvExtractor->extract() );
    }

    /**
     * @throws SourceWatcherException
     */
    public function testGetColumnsWithDifferentNames () : void
    {
        $csvExtractor = new CsvExtractor();
        $csvExtractor->setColumns( [
            $this->idIndex => $this->idIndex,
            $this->emailIndex => $this->emailAddressIndex
        ] );
        $csvExtractor->setInput( new FileInput( $this->csvLocation ) );

        $expected = array_map( function ( array $row ) {
            return new Row( [ $this->idIndex => $row["id"], $this->emailAddressIndex => $row["email"] ] );
        }, $this->allRowsExpected );

        $this->assertEquals( $expected, $csvExtractor->extract() );
    }

    public function testSetGetOverrideHeaders () : void
    {
        $csvExtractor = new CsvExtractor();
        $csvExtractor->setOverrideHeaders( "1" );
        $this->assertSame( "1", $csvExtractor->getOverrideHeaders() );
    }

    public function testSetGetRegexChange () : void
    {
        $csvExtractor = new CsvExtractor();
        $given = [ "regex" => "/a/", "callback" => function ( $line ) { return $line; } ];
        $csvExtractor->setRegexChange( $given );
        $this->assertSame( $given, $csvExtractor->getRegexChange() );
    }

    public function testSetGetResumeRowAndResumeRowByField () : void
    {
        $csvExtractor = new CsvExtractor();
        $row = new Row( [ "id" => 2 ] );
        $csvExtractor->setResumeRow( $row );
        $csvExtractor->setResumeRowByField( "id" );
        $this->assertSame( $row, $csvExtractor->getResumeRow() );
        $this->assertSame( "id", $csvExtractor->getResumeRowByField() );
    }

    /**
     * extract() with overrideHeaders: no header line, columns set manually
     *
     * @throws SourceWatcherException
     */
    public function testExtractWithOverrideHeaders () : void
    {
        $tmp = tempnam( sys_get_temp_dir(), "csv" );
        file_put_contents( $tmp, "1,John,john@e.com\n2,Jane,jane@e.com\n" );

        $csvExtractor = new CsvExtractor();
        $csvExtractor->setInput( new FileInput( $tmp ) );
        $csvExtractor->setColumns( [ "id" => 1, "name" => 2, "email" => 3 ] );
        $csvExtractor->setOverrideHeaders( "1" );

        $result = $csvExtractor->extract();
        @unlink( $tmp );

        $this->assertCount( 2, $result );
        $this->assertEquals( "1", $result[0]["id"] );
        $this->assertEquals( "John", $result[0]["name"] );
        $this->assertEquals( "2", $result[1]["id"] );
        $this->assertEquals( "Jane", $result[1]["name"] );
    }

    /**
     * extract() with resumeRow/resumeRowByField: skip until match, then include only rows after
     *
     * @throws SourceWatcherException
     */
    public function testExtractWithResumeRow () : void
    {
        $tmp = tempnam( sys_get_temp_dir(), "csv" );
        file_put_contents( $tmp, "id,name\n1,Alice\n2,Bob\n3,Carol\n" );

        $csvExtractor = new CsvExtractor();
        $csvExtractor->setInput( new FileInput( $tmp ) );
        $csvExtractor->setResumeRow( new Row( [ "id" => "2", "name" => "Bob" ] ) );
        $csvExtractor->setResumeRowByField( "id" );

        $result = $csvExtractor->extract();
        @unlink( $tmp );

        $this->assertCount( 1, $result );
        $this->assertEquals( "3", $result[0]["id"] );
        $this->assertEquals( "Carol", $result[0]["name"] );
    }

    /**
     * Row with fewer columns than header: missing index yields empty string (generateRow branch)
     *
     * @throws SourceWatcherException
     */
    public function testExtractWithShortRow () : void
    {
        $tmp = tempnam( sys_get_temp_dir(), "csv" );
        file_put_contents( $tmp, "id,name,email\n1,John Doe\n2,Jane Doe,jane@e.com\n" );

        $csvExtractor = new CsvExtractor();
        $csvExtractor->setInput( new FileInput( $tmp ) );

        $result = $csvExtractor->extract();
        @unlink( $tmp );

        $this->assertCount( 2, $result );
        $this->assertEquals( "1", $result[0]["id"] );
        $this->assertEquals( "John Doe", $result[0]["name"] );
        $this->assertSame( "", $result[0]["email"] );
        $this->assertEquals( "2", $result[1]["id"] );
        $this->assertEquals( "Jane Doe", $result[1]["name"] );
        $this->assertEquals( "jane@e.com", $result[1]["email"] );
    }

    /**
     * extract() with regexChange: callback is applied to each line (covers regex branch)
     *
     * @throws SourceWatcherException
     */
    public function testExtractWithRegexChange () : void
    {
        $tmp = tempnam( sys_get_temp_dir(), "csv" );
        file_put_contents( $tmp, "id,name\n1,John\n2,Jane\n" );

        $csvExtractor = new CsvExtractor();
        $csvExtractor->setInput( new FileInput( $tmp ) );
        $csvExtractor->setRegexChange( [
            "regex" => "/^(\d+),(\w+)$/",
            "callback" => function ( $line, $matches ) {
                return $line;
            }
        ] );

        $result = $csvExtractor->extract();
        @unlink( $tmp );

        $this->assertCount( 2, $result );
        $this->assertEquals( "1", $result[0]["id"] );
        $this->assertEquals( "John", $result[0]["name"] );
    }
}
