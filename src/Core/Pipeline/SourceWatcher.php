<?php

namespace Coco\SourceWatcher\Core\Pipeline;

use Coco\SourceWatcher\Core\Exception\SourceWatcherException;
use Coco\SourceWatcher\Core\IO\Inputs\Input;
use Coco\SourceWatcher\Core\IO\Outputs\Output;
use Coco\SourceWatcher\Core\Step\Extractor;
use Coco\SourceWatcher\Core\Step\Loader;
use Coco\SourceWatcher\Core\Step\Transformer;
use Coco\SourceWatcher\Utils\Internationalization;
use Ramsey\Uuid\Uuid;


/**
 * Class SourceWatcher
 *
 * @package Coco\SourceWatcher\Core\Pipeline
 */
class SourceWatcher
{
    private StepLoader $stepLoader;

    private Pipeline $pipeline;

    public function __construct ()
    {
        $this->stepLoader = new StepLoader();
        $this->pipeline = new Pipeline();
    }

    public function getStepLoader () : StepLoader
    {
        return $this->stepLoader;
    }

    public function getPipeline () : Pipeline
    {
        return $this->pipeline;
    }

    /**
     * @param string $extractorName
     * @param Input|null $input
     * @param array $options
     * @return $this
     * @throws SourceWatcherException
     */
    public function extract ( string $extractorName, ?Input $input, array $options = [] ) : SourceWatcher
    {
        $extractor = $this->stepLoader->getStep( Extractor::class, $extractorName );

        if ( $extractor == null ) {
            throw new SourceWatcherException(
                sprintf(
                    Internationalization::getInstance()->getText( SourceWatcher::class, "Extractor_Not_Found" ),
                    $extractorName
                )
            );
        }

        $extractor->setInput( $input );
        $extractor->options( $options );

        $this->pipeline->pipe( $extractor );

        return $this;
    }

    /**
     * @param string $transformerName
     * @param array $options
     * @return $this
     * @throws SourceWatcherException
     */
    public function transform ( string $transformerName, array $options = [] ) : SourceWatcher
    {
        $transformer = $this->stepLoader->getStep( Transformer::class, $transformerName );

        if ( $transformer == null ) {
            throw new SourceWatcherException(
                sprintf(
                    Internationalization::getInstance()->getText( SourceWatcher::class, "Transformer_Not_Found" ),
                    $transformerName
                )
            );
        }

        $transformer->options( $options );

        $this->pipeline->pipe( $transformer );

        return $this;
    }

    /**
     * @param string $loaderName
     * @param Output $output
     * @param array $options
     * @return $this
     * @throws SourceWatcherException
     */
    public function load ( string $loaderName, Output $output, array $options = [] ) : SourceWatcher
    {
        $loader = $this->stepLoader->getStep( Loader::class, $loaderName );

        if ( $loader == null ) {
            throw new SourceWatcherException(
                sprintf(
                    Internationalization::getInstance()->getText( SourceWatcher::class, "Loader_Not_Found" ),
                    $loaderName
                )
            );
        }

        $loader->setOutput( $output );
        $loader->options( $options );

        $this->pipeline->pipe( $loader );

        return $this;
    }

    public function run () : void
    {
        $this->pipeline->execute();
    }

    public function flush () : void
    {
        $this->pipeline = new Pipeline();
    }

    public function save ( ?string $name = null ) : string
    {
        $arrayRepresentation = [];

        $steps = $this->pipeline->getSteps();

        foreach ( $steps as $currentStep ) {
            $arrayRepresentation[] = $currentStep->getArrayRepresentation();
        }

        $jsonRepresentation = json_encode( $arrayRepresentation, JSON_PRETTY_PRINT );

        $mainDirectory = $_SERVER["HOME"] . DIRECTORY_SEPARATOR . ".source-watcher";

        if ( !file_exists( $mainDirectory ) ) {
            mkdir( $mainDirectory, 0777, true );
        }

        $transformationsDirectory = $mainDirectory . DIRECTORY_SEPARATOR . "transformations";

        if ( !file_exists( $transformationsDirectory ) ) {
            mkdir( $transformationsDirectory, 0777, true );
        }

        if ( empty( $name ) ) {
            $uuid = Uuid::uuid4();

            $name = $uuid->toString();
        }

        $transformationFile = $transformationsDirectory . DIRECTORY_SEPARATOR . $name . ".swt";

        file_put_contents( $transformationFile, $jsonRepresentation );

        return $transformationFile;
    }
}
