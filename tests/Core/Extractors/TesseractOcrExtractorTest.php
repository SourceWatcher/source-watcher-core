<?php declare( strict_types=1 );

namespace Coco\SourceWatcher\Tests\Core\Extractors;

use Coco\SourceWatcher\Core\Data\Row;
use Coco\SourceWatcher\Core\Exception\SourceWatcherException;
use Coco\SourceWatcher\Core\Extractors\TesseractOcrExtractor;
use Coco\SourceWatcher\Core\IO\Inputs\DatabaseInput;
use Coco\SourceWatcher\Core\IO\Inputs\FileInput;
use PHPUnit\Framework\TestCase;

class TesseractOcrExtractorTest extends TestCase
{
    public function testDefaultColumnIsText () : void
    {
        $extractor = new TesseractOcrExtractor();
        $this->assertSame( 'text', $extractor->getColumn() );
    }

    public function testDefaultLanguageIsEng () : void
    {
        $extractor = new TesseractOcrExtractor();
        $this->assertSame( 'eng', $extractor->getLanguage() );
    }

    public function testSetGetColumn () : void
    {
        $extractor = new TesseractOcrExtractor();
        $extractor->setColumn( 'content' );
        $this->assertSame( 'content', $extractor->getColumn() );
    }

    public function testSetGetLanguage () : void
    {
        $extractor = new TesseractOcrExtractor();
        $extractor->setLanguage( 'fra' );
        $this->assertSame( 'fra', $extractor->getLanguage() );
    }

    public function testExtractThrowsWhenInputIsNull () : void
    {
        $extractor = new TesseractOcrExtractor();
        $this->expectException( SourceWatcherException::class );
        $this->expectExceptionMessage( 'An input must be provided.' );
        $extractor->extract();
    }

    public function testExtractThrowsWhenInputIsNotFileInput () : void
    {
        $extractor = new TesseractOcrExtractor();
        $extractor->setInput( new DatabaseInput( null ) );
        $this->expectException( SourceWatcherException::class );
        $this->expectExceptionMessage( FileInput::class );
        $extractor->extract();
    }

    /**
     * Integration test: skipped automatically when tesseract is not installed.
     * Creates a minimal PNG with text via GD, runs the extractor, and checks
     * that at least one Row is returned with the expected column key.
     *
     * @requires extension gd
     */
    public function testExtractReturnsRowsFromImage () : void
    {
        if ( shell_exec( 'which tesseract 2>/dev/null' ) === null ) {
            $this->markTestSkipped( 'tesseract is not installed on this system.' );
        }

        $image = imagecreatetruecolor( 300, 50 );
        $white = imagecolorallocate( $image, 255, 255, 255 );
        $black = imagecolorallocate( $image, 0, 0, 0 );
        imagefill( $image, 0, 0, $white );
        imagestring( $image, 5, 10, 15, 'Hello World', $black );

        $tmpFile = tempnam( sys_get_temp_dir(), 'ocr_test_' ) . '.png';
        imagepng( $image, $tmpFile );

        try {
            $extractor = new TesseractOcrExtractor();
            $extractor->setInput( new FileInput( $tmpFile ) );
            $extractor->setColumn( 'line' );

            $result = $extractor->extract();

            $this->assertIsArray( $result );
            $this->assertNotEmpty( $result, 'Expected at least one OCR output row.' );
            $this->assertInstanceOf( Row::class, $result[0] );
            $this->assertArrayHasKey( 'line', $result[0]->getAttributes() );
        } finally {
            if ( file_exists( $tmpFile ) ) {
                @unlink( $tmpFile );
            }
        }
    }

    /**
     * Integration test: skipped when tesseract is not installed.
     * Verifies that empty lines produced by Tesseract are filtered out.
     *
     * @requires extension gd
     */
    public function testExtractFiltersEmptyLines () : void
    {
        if ( shell_exec( 'which tesseract 2>/dev/null' ) === null ) {
            $this->markTestSkipped( 'tesseract is not installed on this system.' );
        }

        $image = imagecreatetruecolor( 300, 50 );
        $white = imagecolorallocate( $image, 255, 255, 255 );
        imagefill( $image, 0, 0, $white );

        $tmpFile = tempnam( sys_get_temp_dir(), 'ocr_blank_' ) . '.png';
        imagepng( $image, $tmpFile );

        try {
            $extractor = new TesseractOcrExtractor();
            $extractor->setInput( new FileInput( $tmpFile ) );

            $result = $extractor->extract();

            $this->assertIsArray( $result, 'extract() must return an array.' );
            foreach ( $result as $row ) {
                $values = array_values( $row->getAttributes() );
                $this->assertNotSame( '', $values[0], 'Empty lines must be filtered.' );
            }
        } finally {
            if ( file_exists( $tmpFile ) ) {
                @unlink( $tmpFile );
            }
        }
    }
}
