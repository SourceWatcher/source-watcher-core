<?php

namespace Coco\SourceWatcher\Core\Extractors;

use Coco\SourceWatcher\Core\Step\Extractor;
use Coco\SourceWatcher\Core\IO\Inputs\ExtractorResultInput;
use Coco\SourceWatcher\Core\Exception\SourceWatcherException;

/**
 * Class ExecutionExtractor
 *
 * @package Coco\SourceWatcher\Core\Extractors
 */
class ExecutionExtractor extends Extractor
{
    /**
     * @return array
     * @throws SourceWatcherException
     */
    public function extract ()
    {
        if ( $this->input == null ) {
            throw new SourceWatcherException( "An input must be provided." );
        }

        $inputIsValid = $this->input instanceof ExtractorResultInput;

        if ( !$inputIsValid ) {
            throw new SourceWatcherException( sprintf( "The input must be an instance of %s",
                ExtractorResultInput::class ) );
        }

        $previousExtractor = $this->input->getInput();

        $this->result = $previousExtractor->getResult();

        return $this->result;
    }
}
