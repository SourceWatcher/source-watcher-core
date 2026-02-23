<?php

namespace Coco\SourceWatcher\Core\Transformers;

use Coco\SourceWatcher\Core\Data\Row;
use Coco\SourceWatcher\Core\Step\Transformer;

/**
 * Class RenameColumnsTransformer
 * @package Coco\SourceWatcher\Core\Transformers
 */
class RenameColumnsTransformer extends Transformer
{
    protected array $columns = [];

    protected array $availableOptions = [ "columns" ];

    public function transform ( Row $row )
    {
        foreach ( $this->columns as $oldColumnName => $newColumnName ) {
            $value = $row->get( $oldColumnName );
            $row->remove( $oldColumnName );
            $row->set( $newColumnName, $value );
        }
    }
}
