<?php

namespace Coco\SourceWatcher\Core\Step;

use Coco\SourceWatcher\Utils\TextUtils;

/**
 * @package Coco\SourceWatcher\Core\Step
 */
class Step
{
    protected ?string $description = null;

    protected array $availableOptions = [];

    public function options ( array $options ) : void
    {
        foreach ( $options as $optionName => $optionValue ) {
            if ( strtolower( $optionName ) === "description" ) {
                $this->description = is_string( $optionValue ) ? $optionValue : null;
                continue;
            }

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
        $result = [
            "type" => $this->getType(),
            "class" => get_class( $this ),
            "description" => $this->description,
            "options" => []
        ];

        foreach ( $this->availableOptions as $currentOption ) {
            $result["options"][$currentOption] = $this->$currentOption;
        }

        return $result;
    }
}
