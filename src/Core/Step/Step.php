<?php

namespace Coco\SourceWatcher\Core\Step;

use Coco\SourceWatcher\Utils\TextUtils;

/**
 * @package Coco\SourceWatcher\Core\Step
 */
class Step
{
    protected array $availableOptions = [];

    public function options ( array $options ) : void
    {
        foreach ( $options as $optionName => $optionValue ) {
            $camelCaseOptionName = $this->textToCamelCase( $optionName );

            if ( in_array( $camelCaseOptionName, $this->availableOptions ) ) {
                $this->$camelCaseOptionName = $optionValue;
            }
        }
    }

    protected function textToCamelCase ( string $word ) : string
    {
        $textUtils = new TextUtils();

        return $textUtils->textToCamelCase( $word );
    }

    public function getType () : string
    {
        if ( $this instanceof Extractor ) {
            return "Extractor";
        }

        if ( $this instanceof Transformer ) {
            return "Transformer";
        }

        if ( $this instanceof Loader ) {
            return "Loader";
        }
    }

    public function getArrayRepresentation () : array
    {
        $result = [ "type" => $this->getType(), "class" => get_class( $this ), "options" => [] ];

        foreach ( $this->availableOptions as $currentOption ) {
            $result["options"][$currentOption] = $this->$currentOption;
        }

        return $result;
    }
}
