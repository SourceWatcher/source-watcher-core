<?php declare( strict_types=1 );

namespace Coco\SourceWatcher\Tests\Core\IO\Inputs;

use Coco\SourceWatcher\Core\Extractors\JsonExtractor;
use Coco\SourceWatcher\Core\IO\Inputs\ExtractorResultInput;
use Coco\SourceWatcher\Core\IO\Inputs\FileInput;
use PHPUnit\Framework\TestCase;

class ExtractorResultInputTest extends TestCase
{
    public function testConstructorAndGetInput () : void
    {
        $extractor = new JsonExtractor();
        $input = new ExtractorResultInput( $extractor );
        $this->assertSame( $extractor, $input->getInput() );
    }

    public function testConstructorAcceptsNull () : void
    {
        $input = new ExtractorResultInput( null );
        $this->assertNull( $input->getInput() );
    }

    public function testSetInput () : void
    {
        $input = new ExtractorResultInput( null );
        $extractor = new JsonExtractor();
        $input->setInput( $extractor );
        $this->assertSame( $extractor, $input->getInput() );
    }

    public function testSetInputWithNonExtractorStoresNull () : void
    {
        $extractor = new JsonExtractor();
        $input = new ExtractorResultInput( $extractor );
        $input->setInput( new FileInput( "/tmp/foo" ) );
        $this->assertNull( $input->getInput() );
    }
}
