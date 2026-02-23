<?php declare( strict_types=1 );

namespace Coco\SourceWatcher\Tests\Core\Transformers;

use Coco\SourceWatcher\Core\Transformers\JavaTransformerArgument;
use Coco\SourceWatcher\Core\Transformers\JavaTransformerArgumentType;
use Coco\SourceWatcher\Core\Exception\SourceWatcherException;
use PHPUnit\Framework\TestCase;

class JavaTransformerArgumentTest extends TestCase
{
    public function testConstructorColumnType () : void
    {
        $arg = new JavaTransformerArgument( [
            "type" => JavaTransformerArgumentType::ARG_TYPE_COLUMN,
            "columnValue" => "name",
        ] );
        $this->assertSame( JavaTransformerArgumentType::ARG_TYPE_COLUMN, $arg->getType() );
        $this->assertSame( "name", $arg->getColumnValue() );
    }

    public function testConstructorStringType () : void
    {
        $arg = new JavaTransformerArgument( [
            "type" => JavaTransformerArgumentType::ARG_TYPE_STRING,
            "stringValue" => "fixed",
        ] );
        $this->assertSame( JavaTransformerArgumentType::ARG_TYPE_STRING, $arg->getType() );
        $this->assertSame( "fixed", $arg->getStringValue() );
    }

    public function testConstructorMixedType () : void
    {
        $arg = new JavaTransformerArgument( [
            "type" => JavaTransformerArgumentType::ARG_TYPE_MIXED,
            "mixedKey" => "key",
            "mixedVal" => "col",
        ] );
        $this->assertSame( JavaTransformerArgumentType::ARG_TYPE_MIXED, $arg->getType() );
        $this->assertSame( "key", $arg->getMixedKey() );
        $this->assertSame( "col", $arg->getMixedVal() );
    }

    public function testConstructorThrowsForUnknownType () : void
    {
        $this->expectException( SourceWatcherException::class );
        $this->expectExceptionMessage( "Type not supported" );
        new JavaTransformerArgument( [ "type" => "unknown" ] );
    }
}
