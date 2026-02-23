<?php

namespace Coco\SourceWatcher\Tests\Core\Transformers;

use PHPUnit\Framework\TestCase;
use Coco\SourceWatcher\Core\Transformers\RenameColumnsTransformer;
use Coco\SourceWatcher\Core\Data\Row;

/**
 * Class RenameColumnsTransformerTest
 *
 * @package Coco\SourceWatcher\Tests\Core\Transformers
 */
class RenameColumnsTransformerTest extends TestCase
{
    public function testRenameColumns () : void
    {
        $transformer = new RenameColumnsTransformer();
        $transformer->options( [ "columns" => [ "email_address" => "email" ] ] );

        $givenRow = new Row( [ "id" => "1", "name" => "John Doe", "email_address" => "johndoe@email.com" ] );

        $transformer->transform( $givenRow );

        $expectedRow = new Row( [ "id" => "1", "name" => "John Doe", "email" => "johndoe@email.com" ] );

        $this->assertEquals( $expectedRow, $givenRow );
    }
}
