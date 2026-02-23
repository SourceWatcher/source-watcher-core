<?php declare( strict_types=1 );

namespace Coco\SourceWatcher\Tests\Core\Extractors;

use Coco\SourceWatcher\Core\Extractor;
use Coco\SourceWatcher\Core\Extractors\ExecutionExtractor;
use Coco\SourceWatcher\Core\IO\Inputs\ExtractorResultInput;
use Coco\SourceWatcher\Core\Row;
use Coco\SourceWatcher\Core\SourceWatcherException;
use PHPUnit\Framework\TestCase;

/**
 * Stub extractor that returns a pre-set result (for testing ExecutionExtractor).
 */
class StubExtractorForExecution extends Extractor
{
    public function extract ()
    {
        return $this->result ?? [];
    }
}

class ExecutionExtractorTest extends TestCase
{
    public function testExtractThrowsWhenInputIsNull () : void
    {
        $extractor = new ExecutionExtractor();
        $this->expectException( SourceWatcherException::class );
        $this->expectExceptionMessage( "An input must be provided." );
        $extractor->extract();
    }

    public function testExtractThrowsWhenInputIsNotExtractorResultInput () : void
    {
        $extractor = new ExecutionExtractor();
        $extractor->setInput( new \Coco\SourceWatcher\Core\IO\Inputs\FileInput( __FILE__ ) );
        $this->expectException( SourceWatcherException::class );
        $this->expectExceptionMessage( ExtractorResultInput::class );
        $extractor->extract();
    }

    public function testExtractReturnsPreviousExtractorResult () : void
    {
        $previousResult = [ new Row( [ "id" => 1 ] ), new Row( [ "id" => 2 ] ) ];
        $stub = new StubExtractorForExecution();
        $stub->setResult( $previousResult );

        $input = new ExtractorResultInput( $stub );
        $extractor = new ExecutionExtractor();
        $extractor->setInput( $input );

        $result = $extractor->extract();
        $this->assertSame( $previousResult, $result );
    }
}
