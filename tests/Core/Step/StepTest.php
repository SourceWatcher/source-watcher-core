<?php declare( strict_types=1 );

namespace Coco\SourceWatcher\Tests\Core\Step;

use Coco\SourceWatcher\Core\Extractors\ApiExtractor;
use Coco\SourceWatcher\Core\Extractors\TxtExtractor;
use Coco\SourceWatcher\Core\IO\Inputs\ApiInput;
use PHPUnit\Framework\TestCase;

class StepTest extends TestCase
{
    public function testOptionsWithDescriptionSetsDescription () : void
    {
        $step = new TxtExtractor();
        $step->options( [ 'description' => 'Extract lines from a log file', 'column' => 'line' ] );

        $repr = $step->getArrayRepresentation();

        $this->assertSame( 'Extract lines from a log file', $repr['description'] );
        $this->assertSame( 'line', $repr['options']['column'] );
    }

    public function testOptionsWithNonStringDescriptionSetsNull () : void
    {
        $step = new TxtExtractor();
        $step->options( [ 'description' => 42 ] );

        $repr = $step->getArrayRepresentation();

        $this->assertNull( $repr['description'] );
    }

    public function testOptionsDescriptionIsCaseInsensitive () : void
    {
        $step = new TxtExtractor();
        $step->options( [ 'DESCRIPTION' => 'Upper case key' ] );

        $repr = $step->getArrayRepresentation();

        $this->assertSame( 'Upper case key', $repr['description'] );
    }

    public function testDescriptionDefaultsToNull () : void
    {
        $step = new TxtExtractor();

        $repr = $step->getArrayRepresentation();

        $this->assertNull( $repr['description'] );
    }

    public function testApiExtractorArrayRepresentationIncludesInputUrlAndClass () : void
    {
        $url = 'https://api.example.com/data';
        $extractor = new ApiExtractor();
        $extractor->setInput( new ApiInput( $url ) );

        $repr = $extractor->getArrayRepresentation();

        $this->assertArrayHasKey( 'input', $repr );
        $this->assertSame( $url, $repr['input']['url'] );
        $this->assertSame( ApiInput::class, $repr['input']['class'] );
    }
}
