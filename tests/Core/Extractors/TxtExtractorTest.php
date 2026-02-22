<?php declare( strict_types=1 );

namespace Coco\SourceWatcher\Tests\Core\Extractors;

use Coco\SourceWatcher\Core\Extractors\TxtExtractor;
use Coco\SourceWatcher\Core\IO\Inputs\FileInput;
use Coco\SourceWatcher\Core\Row;
use Coco\SourceWatcher\Core\SourceWatcherException;
use PHPUnit\Framework\TestCase;

class TxtExtractorTest extends TestCase
{
    private string $tempFile;

    protected function setUp () : void
    {
        parent::setUp();
        $this->tempFile = (string) tempnam( sys_get_temp_dir(), "txt_extractor_" );
        file_put_contents( $this->tempFile, "line one\nline two\nline three\n" );
    }

    protected function tearDown () : void
    {
        if ( file_exists( $this->tempFile ) ) {
            @unlink( $this->tempFile );
        }
        parent::tearDown();
    }

    public function testExtractThrowsWhenInputIsNull () : void
    {
        $extractor = new TxtExtractor();
        $this->expectException( SourceWatcherException::class );
        $this->expectExceptionMessage( "An input must be provided." );
        $extractor->extract();
    }

    public function testExtractThrowsWhenInputIsNotFileInput () : void
    {
        $extractor = new TxtExtractor();
        $extractor->setInput( new \Coco\SourceWatcher\Core\IO\Inputs\DatabaseInput( null ) );
        $this->expectException( SourceWatcherException::class );
        $this->expectExceptionMessage( FileInput::class );
        $extractor->extract();
    }

    public function testSetGetColumn () : void
    {
        $extractor = new TxtExtractor();
        $extractor->setColumn( "content" );
        $this->assertSame( "content", $extractor->getColumn() );
    }

    public function testExtractReturnsOneRowPerLine () : void
    {
        $extractor = new TxtExtractor();
        $extractor->setInput( new FileInput( $this->tempFile ) );
        $extractor->setColumn( "line" );

        $result = $extractor->extract();

        $this->assertCount( 3, $result );
        $this->assertInstanceOf( Row::class, $result[0] );
        $this->assertSame( "line one", $result[0]["line"] );
        $this->assertSame( "line two", $result[1]["line"] );
        $this->assertSame( "line three", $result[2]["line"] );
    }
}
