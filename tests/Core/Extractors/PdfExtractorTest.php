<?php declare( strict_types=1 );

namespace Coco\SourceWatcher\Tests\Core\Extractors;

use Coco\SourceWatcher\Core\Data\Row;
use Coco\SourceWatcher\Core\Exception\SourceWatcherException;
use Coco\SourceWatcher\Core\Extractors\PdfExtractor;
use Coco\SourceWatcher\Core\IO\Inputs\DatabaseInput;
use Coco\SourceWatcher\Core\IO\Inputs\FileInput;
use PHPUnit\Framework\TestCase;

class PdfExtractorTest extends TestCase
{
    // --- unit tests (no external tools needed) ---

    public function testDefaultColumnIsText () : void
    {
        $extractor = new PdfExtractor();
        $this->assertSame( 'text', $extractor->getColumn() );
    }

    public function testDefaultLanguageIsEng () : void
    {
        $extractor = new PdfExtractor();
        $this->assertSame( 'eng', $extractor->getLanguage() );
    }

    public function testDefaultPageColumnIsPage () : void
    {
        $extractor = new PdfExtractor();
        $this->assertSame( 'page', $extractor->getPageColumn() );
    }

    public function testSetGetColumn () : void
    {
        $extractor = new PdfExtractor();
        $extractor->setColumn( 'content' );
        $this->assertSame( 'content', $extractor->getColumn() );
    }

    public function testSetGetLanguage () : void
    {
        $extractor = new PdfExtractor();
        $extractor->setLanguage( 'fra' );
        $this->assertSame( 'fra', $extractor->getLanguage() );
    }

    public function testSetGetPageColumn () : void
    {
        $extractor = new PdfExtractor();
        $extractor->setPageColumn( 'pg' );
        $this->assertSame( 'pg', $extractor->getPageColumn() );
    }

    public function testPageColumnCanBeDisabledWithEmptyString () : void
    {
        $extractor = new PdfExtractor();
        $extractor->setPageColumn( '' );
        $this->assertSame( '', $extractor->getPageColumn() );
    }

    public function testExtractThrowsWhenInputIsNull () : void
    {
        $extractor = new PdfExtractor();
        $this->expectException( SourceWatcherException::class );
        $this->expectExceptionMessage( 'An input must be provided.' );
        $extractor->extract();
    }

    public function testExtractThrowsWhenInputIsNotFileInput () : void
    {
        $extractor = new PdfExtractor();
        $extractor->setInput( new DatabaseInput( null ) );
        $this->expectException( SourceWatcherException::class );
        $this->expectExceptionMessage( FileInput::class );
        $extractor->extract();
    }

    // --- integration tests (skipped if poppler-utils not installed) ---

    /**
     * Creates a minimal single-page text-layer PDF and verifies that rows are returned
     * with the expected column names and page number.
     */
    public function testExtractFromTextLayerPdfReturnsRows () : void
    {
        if ( shell_exec( 'which pdfinfo 2>/dev/null' ) === null ) {
            $this->markTestSkipped( 'poppler-utils is not installed on this system.' );
        }

        $tmpFile = $this->createTextPdf( 'Source Watch' );
        try {
            $extractor = new PdfExtractor();
            $extractor->setInput( new FileInput( $tmpFile ) );

            $result = $extractor->extract();

            $this->assertIsArray( $result );
            $this->assertNotEmpty( $result, 'Expected at least one row from a text-layer PDF.' );
            $this->assertInstanceOf( Row::class, $result[0] );

            $attrs = $result[0]->getAttributes();
            $this->assertArrayHasKey( 'text', $attrs );
            $this->assertArrayHasKey( 'page', $attrs );
            $this->assertSame( 1, $attrs['page'] );
        } finally {
            @unlink( $tmpFile );
        }
    }

    /**
     * Verifies that setting pageColumn to empty string omits the page key from rows.
     */
    public function testPageColumnIsOmittedWhenSetToEmptyString () : void
    {
        if ( shell_exec( 'which pdfinfo 2>/dev/null' ) === null ) {
            $this->markTestSkipped( 'poppler-utils is not installed on this system.' );
        }

        $tmpFile = $this->createTextPdf( 'Hello World' );
        try {
            $extractor = new PdfExtractor();
            $extractor->setInput( new FileInput( $tmpFile ) );
            $extractor->setPageColumn( '' );

            $result = $extractor->extract();

            $this->assertIsArray( $result );
            $this->assertNotEmpty( $result );

            $attrs = $result[0]->getAttributes();
            $this->assertArrayNotHasKey( 'page', $attrs );
            $this->assertArrayHasKey( 'text', $attrs );
        } finally {
            @unlink( $tmpFile );
        }
    }

    /**
     * Verifies that custom column and pageColumn names are honoured.
     */
    public function testCustomColumnNamesAreUsed () : void
    {
        if ( shell_exec( 'which pdfinfo 2>/dev/null' ) === null ) {
            $this->markTestSkipped( 'poppler-utils is not installed on this system.' );
        }

        $tmpFile = $this->createTextPdf( 'Custom columns' );
        try {
            $extractor = new PdfExtractor();
            $extractor->setInput( new FileInput( $tmpFile ) );
            $extractor->setColumn( 'body' );
            $extractor->setPageColumn( 'pg' );

            $result = $extractor->extract();

            $this->assertIsArray( $result );
            $this->assertNotEmpty( $result );

            $attrs = $result[0]->getAttributes();
            $this->assertArrayHasKey( 'body', $attrs );
            $this->assertArrayHasKey( 'pg', $attrs );
            $this->assertArrayNotHasKey( 'text', $attrs );
            $this->assertArrayNotHasKey( 'page', $attrs );
        } finally {
            @unlink( $tmpFile );
        }
    }

    /**
     * Builds a minimal valid single-page PDF with a text content stream.
     * Offsets are computed dynamically so the xref table is always correct.
     */
    private function createTextPdf ( string $text ) : string
    {
        $safeText = str_replace( [ '(', ')', '\\' ], [ '\\(', '\\)', '\\\\' ], $text );
        $stream = "BT\n/F1 12 Tf\n100 700 Td\n(" . $safeText . ") Tj\nET";
        $streamLen = strlen( $stream );

        $obj1 = "1 0 obj\n<</Type/Catalog/Pages 2 0 R>>\nendobj\n";
        $obj2 = "2 0 obj\n<</Type/Pages/Kids[3 0 R]/Count 1>>\nendobj\n";
        $obj3 = "3 0 obj\n<</Type/Page/Parent 2 0 R/MediaBox[0 0 612 792]/Resources<</Font<</F1 4 0 R>>>>/Contents 5 0 R>>\nendobj\n";
        $obj4 = "4 0 obj\n<</Type/Font/Subtype/Type1/BaseFont/Helvetica>>\nendobj\n";
        $obj5 = "5 0 obj\n<</Length {$streamLen}>>\nstream\n{$stream}\nendstream\nendobj\n";

        $header = "%PDF-1.4\n";
        $off1 = strlen( $header );
        $off2 = $off1 + strlen( $obj1 );
        $off3 = $off2 + strlen( $obj2 );
        $off4 = $off3 + strlen( $obj3 );
        $off5 = $off4 + strlen( $obj4 );
        $xrefOffset = $off5 + strlen( $obj5 );

        $xref  = "xref\n0 6\n";
        $xref .= "0000000000 65535 f \n";
        $xref .= sprintf( "%010d 00000 n \n", $off1 );
        $xref .= sprintf( "%010d 00000 n \n", $off2 );
        $xref .= sprintf( "%010d 00000 n \n", $off3 );
        $xref .= sprintf( "%010d 00000 n \n", $off4 );
        $xref .= sprintf( "%010d 00000 n \n", $off5 );
        $xref .= "trailer\n<</Size 6/Root 1 0 R>>\nstartxref\n{$xrefOffset}\n%%EOF\n";

        $tmpFile = tempnam( sys_get_temp_dir(), 'pdf_test_' ) . '.pdf';
        file_put_contents( $tmpFile, $header . $obj1 . $obj2 . $obj3 . $obj4 . $obj5 . $xref );
        return $tmpFile;
    }
}
