<?php

namespace Coco\SourceWatcher\Core\Step;

use Coco\SourceWatcher\Core\Data\Row;

/**
 * @package Coco\SourceWatcher\Core\Step
 */
abstract class Transformer extends Step
{
    abstract public function transform ( Row $row );
}
