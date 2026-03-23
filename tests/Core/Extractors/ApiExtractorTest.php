<?php declare( strict_types=1 );

namespace Coco\SourceWatcher\Tests\Core\Extractors;

use Coco\SourceWatcher\Core\Api\ApiReader;
use Coco\SourceWatcher\Core\Data\Row;
use Coco\SourceWatcher\Core\Exception\SourceWatcherException;
use Coco\SourceWatcher\Core\Extractors\ApiExtractor;
use Coco\SourceWatcher\Core\IO\Inputs\ApiInput;
use Coco\SourceWatcher\Core\IO\Inputs\FileInput;
use PHPUnit\Framework\TestCase;

class ApiExtractorTest extends TestCase
{
    // --- helpers ---

    /**
     * Returns an ApiExtractor subclass whose ApiReader is replaced by a stub
     * that immediately returns the given $response without making any network call.
     */
    private function makeExtractor ( bool|string $response ) : ApiExtractor
    {
        $stub = $this->createMock( ApiReader::class );
        $stub->method( 'read' )->willReturn( $response );

        return new class ( $stub ) extends ApiExtractor {
            private ApiReader $reader;

            public function __construct ( ApiReader $reader )
            {
                $this->reader = $reader;
            }

            protected function createReader () : ApiReader
            {
                return $this->reader;
            }
        };
    }

    private function inputFor ( string $url, array $headers = [] ) : ApiInput
    {
        return new ApiInput( $url, 10, $headers );
    }

    // --- getters / setters ---

    public function testDefaultColumnsIsEmptyArray () : void
    {
        $extractor = new ApiExtractor();
        $this->assertSame( [], $extractor->getColumns() );
    }

    public function testDefaultResponseTypeIsJson () : void
    {
        $extractor = new ApiExtractor();
        $this->assertSame( 'json', $extractor->getResponseType() );
    }

    public function testSetGetColumns () : void
    {
        $extractor = new ApiExtractor();
        $extractor->setColumns( [ 'name' => '$[*].name' ] );
        $this->assertSame( [ 'name' => '$[*].name' ], $extractor->getColumns() );
    }

    public function testSetGetResponseType () : void
    {
        $extractor = new ApiExtractor();
        $extractor->setResponseType( 'xml' );
        $this->assertSame( 'xml', $extractor->getResponseType() );
    }

    // --- input validation (no network) ---

    public function testExtractThrowsWhenInputIsNull () : void
    {
        $extractor = new ApiExtractor();
        $this->expectException( SourceWatcherException::class );
        $this->expectExceptionMessage( 'No input provided.' );
        $extractor->extract();
    }

    public function testExtractThrowsWhenInputIsNotApiInput () : void
    {
        $extractor = new ApiExtractor();
        $extractor->setInput( new FileInput( '/some/file.txt' ) );
        $this->expectException( SourceWatcherException::class );
        $this->expectExceptionMessage( ApiInput::class );
        $extractor->extract();
    }

    public function testExtractThrowsWhenUrlIsEmpty () : void
    {
        $extractor = new ApiExtractor();
        $extractor->setInput( new ApiInput( null ) );
        $this->expectException( SourceWatcherException::class );
        $this->expectExceptionMessage( 'No resource URL provided.' );
        $extractor->extract();
    }

    // --- reader failure ---

    public function testExtractThrowsWhenApiRequestFails () : void
    {
        $extractor = $this->makeExtractor( false );
        $extractor->setInput( $this->inputFor( 'https://example.com/api' ) );

        $this->expectException( SourceWatcherException::class );
        $this->expectExceptionMessage( 'API request failed' );
        $extractor->extract();
    }

    // --- JSON parsing ---

    public function testExtractThrowsWhenResponseIsInvalidJson () : void
    {
        $extractor = $this->makeExtractor( 'this is not json' );
        $extractor->setInput( $this->inputFor( 'https://example.com/api' ) );

        $this->expectException( SourceWatcherException::class );
        $this->expectExceptionMessage( 'Invalid JSON from API' );
        $extractor->extract();
    }

    public function testExtractReturnsRowsFromJsonArray () : void
    {
        $json = '[{"name":"Alice","age":30},{"name":"Bob","age":25}]';
        $extractor = $this->makeExtractor( $json );
        $extractor->setInput( $this->inputFor( 'https://example.com/api' ) );

        $result = $extractor->extract();

        $this->assertCount( 2, $result );
        $this->assertInstanceOf( Row::class, $result[0] );
        $this->assertSame( 'Alice', $result[0]->getAttributes()['name'] );
        $this->assertSame( 'Bob', $result[1]->getAttributes()['name'] );
    }

    public function testExtractReturnsOneRowFromJsonObject () : void
    {
        $json = '{"name":"Alice","age":30}';
        $extractor = $this->makeExtractor( $json );
        $extractor->setInput( $this->inputFor( 'https://example.com/api' ) );

        $result = $extractor->extract();

        $this->assertCount( 1, $result );
        $this->assertSame( 'Alice', $result[0]->getAttributes()['name'] );
    }

    public function testExtractWrapsJsonScalarInValueKey () : void
    {
        $json = '"hello"';
        $extractor = $this->makeExtractor( $json );
        $extractor->setInput( $this->inputFor( 'https://example.com/api' ) );

        $result = $extractor->extract();

        $this->assertCount( 1, $result );
        $this->assertSame( 'hello', $result[0]->getAttributes()['value'] );
    }

    public function testExtractReturnsRowsFromJsonWithColumns () : void
    {
        $json = '[{"name":"Alice","age":30},{"name":"Bob","age":25}]';
        $extractor = $this->makeExtractor( $json );
        $extractor->setInput( $this->inputFor( 'https://example.com/api' ) );
        $extractor->setColumns( [ 'name' => '$[*].name', 'age' => '$[*].age' ] );

        $result = $extractor->extract();

        $this->assertCount( 2, $result );
        $this->assertSame( 'Alice', $result[0]->getAttributes()['name'] );
        $this->assertSame( 25, $result[1]->getAttributes()['age'] );
    }

    // --- headers forwarded ---

    public function testExtractForwardsHeadersToReader () : void
    {
        $stub = $this->createMock( ApiReader::class );
        $stub->method( 'read' )->willReturn( '[{"id":1}]' );
        $stub->expects( $this->once() )
            ->method( 'setHeaders' )
            ->with( [ 'Authorization: Bearer token' ] );

        $extractor = new class ( $stub ) extends ApiExtractor {
            private ApiReader $reader;
            public function __construct ( ApiReader $r ) { $this->reader = $r; }
            protected function createReader () : ApiReader { return $this->reader; }
        };
        $extractor->setInput( $this->inputFor( 'https://example.com/api', [ 'Authorization: Bearer token' ] ) );

        $extractor->extract();
    }

    // --- XML parsing ---

    public function testExtractReturnsRowsFromXml () : void
    {
        $xml = '<users><user><name>Alice</name><age>30</age></user><user><name>Bob</name><age>25</age></user></users>';
        $extractor = $this->makeExtractor( $xml );
        $extractor->setInput( $this->inputFor( 'https://example.com/api' ) );
        $extractor->setResponseType( 'xml' );

        $result = $extractor->extract();

        $this->assertCount( 2, $result );
        $this->assertSame( 'Alice', $result[0]->getAttributes()['name'] );
        $this->assertSame( '25', $result[1]->getAttributes()['age'] );
    }

    public function testExtractReturnsEmptyArrayFromXmlWithNoChildren () : void
    {
        $xml = '<items/>';
        $extractor = $this->makeExtractor( $xml );
        $extractor->setInput( $this->inputFor( 'https://example.com/api' ) );
        $extractor->setResponseType( 'xml' );

        $result = $extractor->extract();

        $this->assertIsArray( $result );
        $this->assertEmpty( $result );
    }

    public function testExtractThrowsWhenXmlIsInvalid () : void
    {
        $extractor = $this->makeExtractor( 'this is not xml <<<' );
        $extractor->setInput( $this->inputFor( 'https://example.com/api' ) );
        $extractor->setResponseType( 'xml' );

        $this->expectException( SourceWatcherException::class );
        $this->expectExceptionMessage( 'Invalid XML from API' );
        $extractor->extract();
    }

    public function testExtractThrowsWhenJsonPathExpressionIsInvalid () : void
    {
        // '!!!' contains characters the JSONPath lexer cannot parse → JSONPathException
        // which is caught and re-thrown as SourceWatcherException
        $json = '[{"name":"Alice"}]';
        $extractor = $this->makeExtractor( $json );
        $extractor->setInput( $this->inputFor( 'https://example.com/api' ) );
        $extractor->setColumns( [ 'name' => '$[!!!]' ] );

        $this->expectException( SourceWatcherException::class );
        $this->expectExceptionMessage( 'JSON Path error' );
        $extractor->extract();
    }

    /**
     * Verifies that the real createReader() factory method returns an ApiReader instance.
     * Covers the factory method itself (used in production; overridden in all other tests).
     */
    public function testCreateReaderReturnsApiReaderInstance () : void
    {
        $extractor = new ApiExtractor();
        $method = ( new \ReflectionClass( $extractor ) )->getMethod( 'createReader' );
        $result = $method->invoke( $extractor );

        $this->assertInstanceOf( ApiReader::class, $result );
    }
}
