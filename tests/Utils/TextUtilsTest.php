<?php declare( strict_types=1 );

namespace Coco\SourceWatcher\Tests\Utils;

use Coco\SourceWatcher\Utils\TextUtils;
use PHPUnit\Framework\TestCase;

/**
 * Class TextUtilsTest
 *
 * @package Coco\SourceWatcher\Tests\Utils
 */
class TextUtilsTest extends TestCase
{
    public function testConvertToCamelCase () : void
    {
        $textUtils = new TextUtils();

        $given = "this_is_a_text";
        $expected = "thisIsAText";

        $this->assertEquals( $expected, $textUtils->textToCamelCase( $given ) );
    }

    public function testConvertToPascalCase () : void
    {
        $textUtils = new TextUtils();

        $given = "this_is_a_text";
        $expected = "ThisIsAText";

        $this->assertEquals( $expected, $textUtils->textToPascalCase( $given ) );
    }

    public function testFromCamelToSnakeCase () : void
    {
        $textUtils = new TextUtils();
        $this->assertSame( "camel_case", $textUtils->fromCamelToSnakeCase( "camelCase" ) );
        $this->assertSame( "simple", $textUtils->fromCamelToSnakeCase( "simple" ) );
    }

    /**
     * All-caps token in camelCase (e.g. URL, ID) uses strtolower branch
     */
    public function testFromCamelToSnakeCaseWithAllCapsSegment () : void
    {
        $textUtils = new TextUtils();
        $this->assertSame( "get_url_path", $textUtils->fromCamelToSnakeCase( "getURLPath" ) );
    }
}
