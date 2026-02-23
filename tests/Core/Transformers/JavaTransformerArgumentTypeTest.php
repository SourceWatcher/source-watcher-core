<?php declare( strict_types=1 );

namespace Coco\SourceWatcher\Tests\Core\Transformers;

use Coco\SourceWatcher\Core\Transformers\JavaTransformerArgumentType;
use PHPUnit\Framework\TestCase;

class JavaTransformerArgumentTypeTest extends TestCase
{
    public function testConstantsAreDefined () : void
    {
        $this->assertSame( "arg_type_column", JavaTransformerArgumentType::ARG_TYPE_COLUMN );
        $this->assertSame( "arg_type_string", JavaTransformerArgumentType::ARG_TYPE_STRING );
        $this->assertSame( "arg_type_mixed", JavaTransformerArgumentType::ARG_TYPE_MIXED );
    }
}
