<?php

namespace Coco\SourceWatcher\Core\Step;

use Coco\SourceWatcher\Core\Extractors\ApiExtractor;
use Coco\SourceWatcher\Core\Extractors\CsvExtractor;
use Coco\SourceWatcher\Core\Extractors\JsonExtractor;
use Coco\SourceWatcher\Core\IO\Inputs\Input;

/**
 * Class Extractor
 *
 * @package Coco\SourceWatcher\Core\Step
 */
abstract class Extractor extends Step
{
    protected ?Input $input = null;

    public function getInput () : Input
    {
        return $this->input;
    }

    public function setInput ( ?Input $input ) : void
    {
        $this->input = $input;
    }

    public abstract function extract ();

    /**
     * @var array
     */
    protected array $result;

    /**
     * @return array
     */
    public function getResult () : array
    {
        return $this->result;
    }

    /**
     * @param array $result
     */
    public function setResult ( array $result ) : void
    {
        $this->result = $result;
    }

    public function getArrayRepresentation () : array
    {
        $result = parent::getArrayRepresentation();

        if ( $this instanceof CsvExtractor || $this instanceof JsonExtractor ) {
            $fileInput = $this->getInput();

            $result["input"] = [ "class" => get_class( $fileInput ), "location" => $fileInput->getInput() ];
        }

        if ( $this instanceof ApiExtractor ) {
            $apiInput = $this->getInput();
            $result["input"] = [ "class" => get_class( $apiInput ), "url" => $apiInput->getInput() ];
        }

        return $result;
    }
}
