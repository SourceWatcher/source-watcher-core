<?php declare( strict_types=1 );

namespace Coco\SourceWatcher\Tests\Core\Transformers;

use Coco\SourceWatcher\Core\Transformers\JavaTransformerResultType;
use PHPUnit\Framework\TestCase;

class JavaTransformerResultTypeTest extends TestCase
{
    public function testConstantIsDefined () : void
    {
        $this->assertSame( "result_type_json", JavaTransformerResultType::RESULT_TYPE_JSON );
    }
}
