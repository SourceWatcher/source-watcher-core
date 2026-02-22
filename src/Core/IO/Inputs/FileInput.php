<?php

namespace Coco\SourceWatcher\Core\IO\Inputs;

/**
 * Class FileInput
 * @package Coco\SourceWatcher\Core\Inputs
 */
class FileInput extends Input
{
    private ?string $fileLocation;

    public function __construct ( ?string $fileLocation = null )
    {
        $this->fileLocation = $fileLocation;
    }

    public function getInput ()
    {
        return $this->fileLocation;
    }

    public function setInput ( $input )
    {
        $this->fileLocation = $input;
    }
}
