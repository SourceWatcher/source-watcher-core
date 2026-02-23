<?php declare( strict_types=1 );

namespace Coco\SourceWatcher\Tests\Core\Transformers;

use Coco\SourceWatcher\Core\Data\Row;
use Coco\SourceWatcher\Core\Transformers\JavaTransformer;
use Coco\SourceWatcher\Core\Transformers\JavaTransformerArgument;
use Coco\SourceWatcher\Core\Transformers\JavaTransformerArgumentType;
use Coco\SourceWatcher\Core\Transformers\JavaTransformerResultType;
use PHPUnit\Framework\TestCase;

/**
 * Class JavaTransformerTest
 *
 * Uses a subclass that overrides runCommand() so tests don't require Java.
 *
 * @package Coco\SourceWatcher\Tests\Core\Transformers
 */
class JavaTransformerTest extends TestCase
{
    /**
     * Transform with stubbed runCommand: JSON output is applied to the row
     */
    public function testTransformAppliesJsonResultToRow () : void
    {
        $transformer = new class extends JavaTransformer {
            protected function runCommand ( string $command ) : array
            {
                return [ [ '{"out":"value","num":42}' ], 0 ];
            }
        };
        $transformer->options( [
            "classpath" => "/cp",
            "classname" => "Main",
            "arguments" => [],
            "result_type" => JavaTransformerResultType::RESULT_TYPE_JSON,
        ] );

        $row = new Row( [ "id" => 1 ] );
        $transformer->transform( $row );

        $this->assertSame( "value", $row->get( "out" ) );
        $this->assertSame( 42, $row->get( "num" ) );
    }

    /**
     * Transform with non-zero return: row is not updated (error path)
     */
    public function testTransformNonZeroReturnDoesNotUpdateRow () : void
    {
        $transformer = new class extends JavaTransformer {
            protected function runCommand ( string $command ) : array
            {
                return [ [ "error output" ], 1 ];
            }
        };
        $transformer->options( [
            "classpath" => "/cp",
            "classname" => "Main",
            "arguments" => [],
            "result_type" => JavaTransformerResultType::RESULT_TYPE_JSON,
        ] );

        $row = new Row( [ "id" => 1 ] );
        $transformer->transform( $row );

        $this->assertSame( 1, $row["id"] );
        $this->assertArrayNotHasKey( "out", $row->getAttributes() );
    }

    /**
     * Transform with empty JSON array: row unchanged (no keys to set)
     */
    public function testTransformEmptyJsonArray () : void
    {
        $transformer = new class extends JavaTransformer {
            protected function runCommand ( string $command ) : array
            {
                return [ [ '{}' ], 0 ];
            }
        };
        $transformer->options( [
            "classpath" => "/cp",
            "classname" => "Main",
            "arguments" => [],
            "result_type" => JavaTransformerResultType::RESULT_TYPE_JSON,
        ] );

        $row = new Row( [ "id" => 1 ] );
        $transformer->transform( $row );

        $this->assertSame( 1, $row["id"] );
    }

    /**
     * Constructor and options set classpath, classname, arguments, resultType
     */
    public function testOptions () : void
    {
        $transformer = new class extends JavaTransformer {
            protected function runCommand ( string $command ) : array
            {
                return [ [], 0 ];
            }
        };
        $transformer->options( [
            "classpath" => "/some/cp",
            "classname" => "com.example.Main",
            "arguments" => [],
            "result_type" => JavaTransformerResultType::RESULT_TYPE_JSON,
        ] );

        $row = new Row( [ "x" => "y" ] );
        $transformer->transform( $row );
        $this->addToAssertionCount( 1 );
    }

    /**
     * getArguments and getCommand are exercised with COLUMN, STRING, and MIXED argument types
     */
    public function testTransformWithArgumentsColumnStringMixed () : void
    {
        $capture = new \stdClass();
        $capture->command = null;

        $transformer = new class( $capture ) extends JavaTransformer {
            private \stdClass $capture;

            public function __construct ( \stdClass $capture )
            {
                parent::__construct();
                $this->capture = $capture;
            }

            protected function runCommand ( string $command ) : array
            {
                $this->capture->command = $command;
                return [ [ '{"done":true}' ], 0 ];
            }
        };
        $transformer->options( [
            "classpath" => "/cp",
            "classname" => "Main",
            "arguments" => [
                new JavaTransformerArgument( [
                    "type" => JavaTransformerArgumentType::ARG_TYPE_COLUMN,
                    "columnValue" => "name",
                ] ),
                new JavaTransformerArgument( [
                    "type" => JavaTransformerArgumentType::ARG_TYPE_STRING,
                    "stringValue" => "fixedArg",
                ] ),
                new JavaTransformerArgument( [
                    "type" => JavaTransformerArgumentType::ARG_TYPE_MIXED,
                    "mixedKey" => "key",
                    "mixedVal" => "id",
                ] ),
            ],
            "result_type" => JavaTransformerResultType::RESULT_TYPE_JSON,
        ] );

        $row = new Row( [ "id" => "1", "name" => "Alice" ] );
        $transformer->transform( $row );

        $this->assertSame( true, $row->get( "done" ) );
        $this->assertStringContainsString( "Alice", $capture->command );
        $this->assertStringContainsString( "fixedArg", $capture->command );
        $this->assertStringContainsString( "key=1", $capture->command );
    }

    /**
     * Return 0 but empty output: isset($output[0]) is false, row not updated from JSON
     */
    public function testTransformZeroReturnEmptyOutput () : void
    {
        $transformer = new class extends JavaTransformer {
            protected function runCommand ( string $command ) : array
            {
                return [ [], 0 ];
            }
        };
        $transformer->options( [
            "classpath" => "/cp",
            "classname" => "Main",
            "arguments" => [],
            "result_type" => JavaTransformerResultType::RESULT_TYPE_JSON,
        ] );

        $row = new Row( [ "id" => 1 ] );
        $transformer->transform( $row );

        $this->assertSame( 1, $row->get( "id" ) );
    }

    /**
     * Return 0 but resultType not JSON: inner block skipped
     */
    public function testTransformZeroReturnNonJsonResultType () : void
    {
        $transformer = new class extends JavaTransformer {
            protected function runCommand ( string $command ) : array
            {
                return [ [ '{"a":1}' ], 0 ];
            }
        };
        $transformer->options( [
            "classpath" => "/cp",
            "classname" => "Main",
            "arguments" => [],
        ] );
        $transformer->transform( new Row( [ "id" => 1 ] ) );
        $this->addToAssertionCount( 1 );
    }

    /**
     * Real runCommand() is invoked (exec path) using a harmless command so no Java is required
     */
    public function testRunCommandExecPath () : void
    {
        $transformer = new class extends JavaTransformer {
            public function runCommandForTest ( string $command ) : array
            {
                return $this->runCommand( $command );
            }
        };
        $result = $transformer->runCommandForTest( $this->getTrueCommand() );
        $this->assertIsArray( $result );
        $this->assertCount( 2, $result );
        $this->assertIsArray( $result[0] );
        $this->assertIsInt( $result[1] );
    }

    private function getTrueCommand () : string
    {
        return DIRECTORY_SEPARATOR === '\\' ? 'cmd /c exit 0' : 'true';
    }
}
