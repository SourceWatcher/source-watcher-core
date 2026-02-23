<?php

namespace Coco\SourceWatcher\Core\Transformers;

use Coco\SourceWatcher\Core\Data\Row;
use Coco\SourceWatcher\Core\Step\Transformer;

/**
 * Class ConvertCaseTransformer
 * @package Coco\SourceWatcher\Core\Transformers
 */
class ConvertCaseTransformer extends Transformer
{
    const CONVERT_CASE_MODE_UPPER = MB_CASE_UPPER;

    const CONVERT_CASE_MODE_LOWER = MB_CASE_LOWER;

    const CONVERT_CASE_MODE_TITLE = MB_CASE_TITLE;

    protected array $columns = [];

    protected string $encoding = "UTF-8";

    protected int $mode = self::CONVERT_CASE_MODE_LOWER;

    protected array $availableOptions = [ "columns", "encoding", "mode" ];

    public function transform ( Row $row )
    {
        foreach ( $this->columns as $oldColumnName ) {
            $value = $row->get( $oldColumnName );

            $row->remove( $oldColumnName );

            $newColumnName = mb_convert_case( $oldColumnName, $this->mode, $this->encoding );

            $row->set( $newColumnName, $value );
        }
    }
}
