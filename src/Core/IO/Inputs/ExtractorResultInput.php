<?php

namespace Coco\SourceWatcher\Core\IO\Inputs;

use Coco\SourceWatcher\Core\Step\Extractor;

/**
 * Class ExtractorResultInput
 *
 * @package Coco\SourceWatcher\Core\IO\Inputs
 */
class ExtractorResultInput extends Input
{
    private ?Extractor $previousExtractor;

    public function __construct ( ?Extractor $previousExtractor = null )
    {
        $this->previousExtractor = $previousExtractor;
    }

    public function getInput () : ?Extractor
    {
        return $this->previousExtractor;
    }

    /**
     * @param \Coco\SourceWatcher\Core\Extractor|null $input
     */
    public function setInput ( $input ) : void
    {
        $this->previousExtractor = $input instanceof Extractor ? $input : null;
    }
}
