<?php declare( strict_types=1 );

namespace Coco\SourceWatcher\Tests\Core\Extractors;

use Coco\SourceWatcher\Core\Extractors\JsonExtractor;
use Coco\SourceWatcher\Core\IO\Inputs\FileInput;
use Coco\SourceWatcher\Core\IO\Inputs\Input;
use Coco\SourceWatcher\Core\Data\Row;
use Coco\SourceWatcher\Core\Exception\SourceWatcherException;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * Class JsonExtractorTest
 *
 * @package Coco\SourceWatcher\Tests\Core\Extractors
 */
class JsonExtractorTest extends TestCase
{
    private string $colorIndex;
    private string $allColorsSelector;

    public function setUp () : void
    {
        $this->colorIndex = "color";
        $this->allColorsSelector = "colors.*.color";
    }

    public function testSetGetColumns () : void
    {
        $jsonExtractor = new JsonExtractor();

        $givenColumns = [ $this->colorIndex => $this->allColorsSelector ];
        $expectedColumns = [ $this->colorIndex => $this->allColorsSelector ];

        $jsonExtractor->setColumns( $givenColumns );

        $this->assertEquals( $expectedColumns, $jsonExtractor->getColumns() );
    }

    public function testSetGetInput () : void
    {
        $jsonExtractor = new JsonExtractor();

        $givenInput = new FileInput( "/some/file/path/file.json" );
        $expectedInput = new FileInput( "/some/file/path/file.json" );

        $jsonExtractor->setInput( $givenInput );

        $this->assertEquals( $expectedInput, $jsonExtractor->getInput() );
    }

    /**
     * @throws SourceWatcherException
     */
    public function testExceptionNoInput () : void
    {
        $this->expectException( SourceWatcherException::class );

        $jsonExtractor = new JsonExtractor();

        $jsonExtractor->extract();
    }

    /**
     * @throws SourceWatcherException
     */
    public function testExtractColors () : void
    {
        $jsonExtractor = new JsonExtractor();
        $jsonExtractor->setColumns( [ $this->colorIndex => $this->allColorsSelector ] );
        $jsonExtractor->setInput( new FileInput( __DIR__ . "/../../../samples/data/json/colors.json" ) );

        $expected = [
            new Row( [ $this->colorIndex => "black" ] ),
            new Row( [ $this->colorIndex => "white" ] ),
            new Row( [ $this->colorIndex => "red" ] ),
            new Row( [ $this->colorIndex => "blue" ] ),
            new Row( [ $this->colorIndex => "yellow" ] ),
            new Row( [ $this->colorIndex => "green" ] )
        ];

        $this->assertEquals( $expected, $jsonExtractor->extract() );
    }

    /**
     * @throws SourceWatcherException
     */
    public function testNonexistentPath () : void
    {
        $this->expectException( SourceWatcherException::class );

        $jsonExtractor = new JsonExtractor();
        $jsonExtractor->setInput( new FileInput( "/file/path/this/doest/not/exist/file.json" ) );
        $jsonExtractor->extract();
    }

    /**
     * @throws SourceWatcherException
     */
    public function testWrongColumnSelectorException () : void
    {
        $this->expectException( SourceWatcherException::class );

        $jsonExtractor = new JsonExtractor();
        $jsonExtractor->setColumns( [ $this->colorIndex => "$.bad-!-selector" ] );
        $jsonExtractor->setInput( new FileInput( __DIR__ . "/../../../samples/data/json/colors.json" ) );
        $jsonExtractor->extract();
    }

    /**
     * @throws SourceWatcherException
     */
    public function testNoFileInputProvided () : void
    {
        $this->expectException( SourceWatcherException::class );

        $jsonExtractor = new JsonExtractor();
        $jsonExtractor->setColumns( [ $this->colorIndex => "some.selector" ] );
        $jsonExtractor->setInput( $this->createMock( Input::class ) );
        $jsonExtractor->extract();
    }

    /**
     * Empty string location must throw (File_Input_File_Not_Found branch).
     *
     * @throws SourceWatcherException
     */
    public function testEmptyLocationThrows () : void
    {
        $this->expectException( SourceWatcherException::class );

        $jsonExtractor = new JsonExtractor();
        $jsonExtractor->setInput( new FileInput( '' ) );
        $jsonExtractor->extract();
    }

    /**
     * Null location must throw (File_Input_File_Not_Found branch).
     *
     * @throws SourceWatcherException
     */
    public function testNullLocationThrows () : void
    {
        $this->expectException( SourceWatcherException::class );

        $jsonExtractor = new JsonExtractor();
        $jsonExtractor->setInput( new FileInput( null ) );
        $jsonExtractor->extract();
    }

    /**
     * Local path that exists but is unreadable: file_get_contents returns false,
     * so extract throws (non-URL branch). Skipped when process can still read (e.g. root).
     *
     * @throws SourceWatcherException
     */
    public function testNestedArrayValuesAreJsonEncodedNotArrayString () : void
    {
        $jsonExtractor = new JsonExtractor();
        $jsonExtractor->setInput( new FileInput( __DIR__ . "/../../../samples/data/json/books.json" ) );

        $result = $jsonExtractor->extract();

        $this->assertNotEmpty( $result );
        $firstRow = $result[0];

        // "authors" is a nested array in books.json — it must be stored as a JSON string, not "Array"
        $authors = $firstRow['authors'];
        $this->assertIsString( $authors, 'Nested array must be serialized to a string.' );
        $this->assertNotSame( 'Array', $authors, 'Nested array must not become the literal string "Array".' );
        $this->assertJson( $authors, 'Nested array must be valid JSON.' );
    }

    public function testLocalPathNotFoundThrows () : void
    {
        $path = sys_get_temp_dir() . '/json-extractor-missing-' . uniqid( '', true ) . '.json';

        $this->expectException( SourceWatcherException::class );

        $jsonExtractor = new JsonExtractor();
        $jsonExtractor->setInput( new FileInput( $path ) );
        $jsonExtractor->extract();
    }


    /**
     * URL that cannot be fetched: file_get_contents returns false, so extract throws
     * with the allow_url_fopen message (URL branch).
     *
     * @throws SourceWatcherException
     */
    public function testUrlFetchFailsThrows () : void
    {
        $this->expectException( SourceWatcherException::class );
        $this->expectExceptionMessage( 'Failed to fetch JSON from URL' );

        $jsonExtractor = new JsonExtractor();
        $jsonExtractor->setInput( new FileInput( 'https://localhost:65535/no-such.json' ) );
        $jsonExtractor->extract();
    }

    /**
     * Covers isUrl() http:// branch (distinct from https).
     *
     * @throws SourceWatcherException
     */
    public function testHttpUrlFetchFailsThrows () : void
    {
        $this->expectException( SourceWatcherException::class );
        $this->expectExceptionMessage( 'Failed to fetch JSON from URL' );

        $jsonExtractor = new JsonExtractor();
        $jsonExtractor->setInput( new FileInput( 'http://localhost:65535/no-such.json' ) );
        $jsonExtractor->extract();
    }

    /**
     * Location normalizes JSON-style escaped slashes before URL fetch (str_replace \/ → /).
     *
     * @throws SourceWatcherException
     */
    public function testUrlWithEscapedSlashesNormalizesAndFailsFetch () : void
    {
        $this->expectException( SourceWatcherException::class );
        $this->expectExceptionMessage( 'Failed to fetch JSON from URL' );

        $jsonExtractor = new JsonExtractor();
        $jsonExtractor->setInput( new FileInput( 'https:\/\/localhost:65535/no-such.json' ) );
        $jsonExtractor->extract();
    }

    /**
     * Malformed JSON must throw SourceWatcherException (not TypeError from foreach on null).
     *
     * @throws SourceWatcherException
     */
    public function testInvalidJsonBodyThrows () : void
    {
        $path = sys_get_temp_dir() . '/json-extractor-bad-' . uniqid( '', true ) . '.json';
        file_put_contents( $path, 'not valid json {{{' );

        try {
            $this->expectException( SourceWatcherException::class );
            $this->expectExceptionMessage( 'Invalid JSON' );

            $jsonExtractor = new JsonExtractor();
            $jsonExtractor->setInput( new FileInput( $path ) );
            $jsonExtractor->extract();
        } finally {
            @unlink( $path );
        }
    }

    /**
     * Valid JSON whose root is not an object/array (e.g. literal null) must throw, not foreach on null.
     *
     * @throws SourceWatcherException
     */
    public function testJsonLiteralNullRootThrows () : void
    {
        $path = sys_get_temp_dir() . '/json-extractor-null-root-' . uniqid( '', true ) . '.json';
        file_put_contents( $path, 'null' );

        try {
            $this->expectException( SourceWatcherException::class );
            $this->expectExceptionMessage( 'JSON root must be an array or object' );

            $jsonExtractor = new JsonExtractor();
            $jsonExtractor->setInput( new FileInput( $path ) );
            $jsonExtractor->extract();
        } finally {
            @unlink( $path );
        }
    }

    /**
     * Valid JSON scalar number at root: json_last_error clean but root is not array/object.
     *
     * @throws SourceWatcherException
     */
    public function testJsonScalarNumberRootThrows () : void
    {
        $path = sys_get_temp_dir() . '/json-extractor-num-root-' . uniqid( '', true ) . '.json';
        file_put_contents( $path, '42' );

        try {
            $this->expectException( SourceWatcherException::class );
            $this->expectExceptionMessage( 'JSON root must be an array or object' );

            $jsonExtractor = new JsonExtractor();
            $jsonExtractor->setInput( new FileInput( $path ) );
            $jsonExtractor->extract();
        } finally {
            @unlink( $path );
        }
    }

    public function testEmptyJsonArrayExtractsToZeroRows () : void
    {
        $path = sys_get_temp_dir() . '/json-extractor-empty-arr-' . uniqid( '', true ) . '.json';
        file_put_contents( $path, '[]' );

        try {
            $jsonExtractor = new JsonExtractor();
            $jsonExtractor->setInput( new FileInput( $path ) );
            $this->assertSame( [], $jsonExtractor->extract() );
        } finally {
            @unlink( $path );
        }
    }

    /**
     * normalizeRowForOutput: when json_encode fails on a nested value, falls back to string 'null'.
     */
    public function testNormalizeRowForOutputUsesNullWhenJsonEncodeFails () : void
    {
        $jsonExtractor = new JsonExtractor();
        $method = new ReflectionMethod( JsonExtractor::class, 'normalizeRowForOutput' );

        $handle = tmpfile();
        $this->assertNotFalse( $handle );
        $this->assertIsResource( $handle );
        $out = $method->invoke( $jsonExtractor, [ 'nested' => $handle ] );
        fclose( $handle );

        $this->assertSame( 'null', $out['nested'] );
    }
}
