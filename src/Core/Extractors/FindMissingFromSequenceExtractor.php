<?php

namespace Coco\SourceWatcher\Core\Extractors;

use Coco\SourceWatcher\Core\Data\Row;
use Coco\SourceWatcher\Core\Exception\SourceWatcherException;

/**
 * Class FindMissingFromSequenceExtractor
 *
 * @package Coco\SourceWatcher\Core\Extractors
 */
class FindMissingFromSequenceExtractor extends ExecutionExtractor
{
    protected string $filterField;

    protected array $availableOptions = [ "filterField" ];

    public function __construct ()
    {
        $this->filterField = "id";
    }

    /**
     * @return string
     */
    public function getFilterField () : string
    {
        return $this->filterField;
    }

    /**
     * @param string $filterField
     */
    public function setFilterField ( string $filterField ) : void
    {
        $this->filterField = $filterField;
    }

    /**
     * @return array
     * @throws SourceWatcherException
     */
    public function extract ()
    {
        $previousExtractorResult = parent::extract();

        $copy = [];

        foreach ( $previousExtractorResult as $currentRow ) {
            $copy[] = $currentRow[$this->filterField];
        }

        asort( $copy );

        $min = reset( $copy );
        $max = end( $copy );

        $result = [];

        for ( $i = $min; $i <= $max; $i++ ) {
            if ( !in_array( $i, $copy ) ) {
                $result[] = new Row( [ $this->filterField => $i ] );
            }
        }

        return $result;
    }
}
