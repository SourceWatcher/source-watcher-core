<?php

namespace Coco\SourceWatcher\Core\Pipeline;

use Coco\SourceWatcher\Core\Extractors\ExecutionExtractor;
use Coco\SourceWatcher\Core\IO\Inputs\ExtractorResultInput;
use Coco\SourceWatcher\Core\Step\Extractor;
use Coco\SourceWatcher\Core\Step\Loader;
use Coco\SourceWatcher\Core\Step\Step;
use Coco\SourceWatcher\Core\Step\Transformer;
use Coco\SourceWatcher\Utils\FileUtils;
use Exception;
use Iterator;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Class Pipeline
 *
 * @package Coco\SourceWatcher\Core\Pipeline
 */
class Pipeline implements Iterator
{
    private array $steps = [];

    private array $results = [];

    private Logger $logger;

    /**
     * @param string|null $logsDir Optional logs directory (default: project logs/). Pass a path that cannot be created to force NullHandler in tests.
     */
    public function __construct ( ?string $logsDir = null )
    {
        $this->logger = new Logger( "Connector" );

        if ( $logsDir === null ) {
            $logsDir = FileUtils::file_build_path( __DIR__, "..", "..", "..", "logs" );
        }
        $streamPath = FileUtils::file_build_path( $logsDir,
            "Connector" . "-" . gmdate( "Y-m-d-H-i-s", time() ) . "-" . getmypid() . ".txt" );

        if ( ( is_dir( $logsDir ) || @mkdir( $logsDir, 0755, true ) ) && is_writable( $logsDir ) ) {
            $this->logger->pushHandler( new StreamHandler( $streamPath ), Logger::DEBUG );
        } else {
            $this->logger->pushHandler( new NullHandler(), Logger::DEBUG );
        }
    }

    public function getSteps () : array
    {
        return $this->steps;
    }

    public function setSteps ( array $steps ) : void
    {
        $this->steps = $steps;
    }

    public function pipe ( Step $step ) : void
    {
        if ( $step instanceof ExecutionExtractor ) {
            $step->setInput( new ExtractorResultInput( end( $this->steps ) ) );
        }

        $this->steps[] = $step;
    }

    public function execute () : void
    {
        foreach ( $this->steps as $currentStep ) {
            if ( $currentStep instanceof Extractor ) {
                $this->results = $currentStep->extract();
            }

            if ( $currentStep instanceof Transformer ) {
                foreach ( $this->results as $currentItem ) {
                    $currentStep->transform( $currentItem );
                }
            }

            if ( $currentStep instanceof Loader ) {
                foreach ( $this->results as $currentItem ) {
                    try {
                        $currentStep->load( $currentItem );
                    } catch ( Exception $exception ) {
                        $this->logger->debug( $exception->getMessage() );
                    }
                }
            }
        }
    }

    public function getResults () : array
    {
        return $this->results;
    }

    private int $index = 0;

    public function current () : mixed
    {
        return $this->results[$this->index];
    }

    public function next () : void
    {
        $this->index++;
    }

    public function key () : int
    {
        return $this->index;
    }

    public function valid () : bool
    {
        return isset( $this->results[$this->index] );
    }

    public function rewind () : void
    {
        $this->index = 0;
    }
}
