<?php

namespace Coco\SourceWatcher\Core\Transformers;

use Coco\SourceWatcher\Core\Exception\SourceWatcherException;

class JavaTransformerArgument
{
    private string $type;

    private string $columnValue;
    private string $stringValue;
    private string $mixedKey;
    private string $mixedVal;

    /**
     * JavaTransformerArgument constructor.
     *
     * @param array $options
     * @throws SourceWatcherException
     */
    public function __construct ( array $options )
    {
        switch ( $options["type"] ) {
            case JavaTransformerArgumentType::ARG_TYPE_COLUMN:
                $this->columnValue = $options["columnValue"];
                break;
            case JavaTransformerArgumentType::ARG_TYPE_STRING:
                $this->stringValue = $options["stringValue"];
                break;
            case JavaTransformerArgumentType::ARG_TYPE_MIXED:
                $this->mixedKey = $options["mixedKey"];
                $this->mixedVal = $options["mixedVal"];
                break;
            default:
                throw new SourceWatcherException( "Type not supported" );
        }

        $this->type = $options["type"];
    }

    /**
     * @return mixed|string
     */
    public function getType () : string
    {
        return $this->type;
    }

    /**
     * @return mixed|string
     */
    public function getColumnValue () : string
    {
        return $this->columnValue;
    }

    /**
     * @return mixed|string
     */
    public function getStringValue () : string
    {
        return $this->stringValue;
    }

    /**
     * @return mixed|string
     */
    public function getMixedKey () : string
    {
        return $this->mixedKey;
    }

    /**
     * @return mixed|string
     */
    public function getMixedVal () : string
    {
        return $this->mixedVal;
    }
}
