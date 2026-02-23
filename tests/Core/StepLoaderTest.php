<?php declare( strict_types=1 );

namespace Coco\SourceWatcher\Tests\Core;

use Coco\SourceWatcher\Core\Exception\SourceWatcherException;
use Coco\SourceWatcher\Core\Pipeline\StepLoader;
use Coco\SourceWatcher\Core\Step\Extractor;
use PHPUnit\Framework\TestCase;

/**
 * Class StepLoaderTest
 *
 * @package Coco\SourceWatcher\Tests\Core
 */
class StepLoaderTest extends TestCase
{
    public function testGetInstanceViaStaticMethodNotNull () : void
    {
        $this->assertNotNull( StepLoader::getInstance() );
    }

    public function testGetInstanceViaStaticMethodIsStepLoader () : void
    {
        $this->assertInstanceOf( StepLoader::class, StepLoader::getInstance() );
    }

    /**
     * @throws SourceWatcherException
     */
    public function testGetExtractor () : void
    {
        $stepLoader = new StepLoader();
        $extractor = $stepLoader->getStep( Extractor::class, "csv" );
        $this->assertNotNull( $extractor );
    }

    /**
     * @throws SourceWatcherException
     */
    public function testGetExtractorValidated () : void
    {
        $stepLoader = new StepLoader();
        $extractor = $stepLoader->getStep( Extractor::class, "csv" );
        $this->assertInstanceOf( Extractor::class, $extractor );
    }

    /**
     * @throws SourceWatcherException
     */
    public function testGetExtractorNoParentClassName () : void
    {
        $this->expectException( SourceWatcherException::class );

        $stepLoader = new StepLoader();
        $stepLoader->getStep( "", "csv" );
    }

    /**
     * @throws SourceWatcherException
     */
    public function testGetExtractorNoStepName () : void
    {
        $this->expectException( SourceWatcherException::class );

        $stepLoader = new StepLoader();
        $stepLoader->getStep( Extractor::class, "" );
    }

    /**
     * @throws SourceWatcherException
     */
    public function testGetNonExistingParentStep () : void
    {
        $this->expectException( SourceWatcherException::class );

        $stepLoader = new StepLoader();
        $stepLoader->getStep( "Sequencer", "csv" );
    }

    /**
     * @throws SourceWatcherException
     */
    public function testGetNonExistingStepName () : void
    {
        $stepLoader = new StepLoader();
        $extractor = $stepLoader->getStep( Extractor::class, "rainbow" );
        $this->assertNull( $extractor );
    }
}
