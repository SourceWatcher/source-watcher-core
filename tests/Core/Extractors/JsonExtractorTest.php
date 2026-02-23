<?php declare( strict_types=1 );

namespace Coco\SourceWatcher\Tests\Core\Extractors;

use Coco\SourceWatcher\Core\Extractors\JsonExtractor;
use Coco\SourceWatcher\Core\IO\Inputs\FileInput;
use Coco\SourceWatcher\Core\IO\Inputs\Input;
use Coco\SourceWatcher\Core\Data\Row;
use Coco\SourceWatcher\Core\Exception\SourceWatcherException;
use PHPUnit\Framework\TestCase;

/**
 * Class JsonExtractorTest
 *
 * @package Coco\SourceWatcher\Tests\Core\Extractors
 */
class JsonExtractorTest extends TestCase
{
    private string $colorIndex;
    private string $allColorsSelector;

    public function setUp () : void
    {
        $this->colorIndex = "color";
        $this->allColorsSelector = "colors.*.color";
    }

    public function testSetGetColumns () : void
    {
        $jsonExtractor = new JsonExtractor();

        $givenColumns = [ $this->colorIndex => $this->allColorsSelector ];
        $expectedColumns = [ $this->colorIndex => $this->allColorsSelector ];

        $jsonExtractor->setColumns( $givenColumns );

        $this->assertEquals( $expectedColumns, $jsonExtractor->getColumns() );
    }

    public function testSetGetInput () : void
    {
        $jsonExtractor = new JsonExtractor();

        $givenInput = new FileInput( "/some/file/path/file.json" );
        $expectedInput = new FileInput( "/some/file/path/file.json" );

        $jsonExtractor->setInput( $givenInput );

        $this->assertEquals( $expectedInput, $jsonExtractor->getInput() );
    }

    /**
     * @throws SourceWatcherException
     */
    public function testExceptionNoInput () : void
    {
        $this->expectException( SourceWatcherException::class );

        $jsonExtractor = new JsonExtractor();

        $jsonExtractor->extract();
    }

    /**
     * @throws SourceWatcherException
     */
    public function testExtractColors () : void
    {
        $jsonExtractor = new JsonExtractor();
        $jsonExtractor->setColumns( [ $this->colorIndex => $this->allColorsSelector ] );
        $jsonExtractor->setInput( new FileInput( __DIR__ . "/../../../samples/data/json/colors.json" ) );

        $expected = [
            new Row( [ $this->colorIndex => "black" ] ),
            new Row( [ $this->colorIndex => "white" ] ),
            new Row( [ $this->colorIndex => "red" ] ),
            new Row( [ $this->colorIndex => "blue" ] ),
            new Row( [ $this->colorIndex => "yellow" ] ),
            new Row( [ $this->colorIndex => "green" ] )
        ];

        $this->assertEquals( $expected, $jsonExtractor->extract() );
    }

    /**
     * @throws SourceWatcherException
     */
    public function testNonexistentPath () : void
    {
        $this->expectException( SourceWatcherException::class );

        $jsonExtractor = new JsonExtractor();
        $jsonExtractor->setInput( new FileInput( "/file/path/this/doest/not/exist/file.json" ) );
        $jsonExtractor->extract();
    }

    /**
     * @throws SourceWatcherException
     */
    public function testWrongColumnSelectorException () : void
    {
        $this->expectException( SourceWatcherException::class );

        $jsonExtractor = new JsonExtractor();
        $jsonExtractor->setColumns( [ $this->colorIndex => "$.bad-!-selector" ] );
        $jsonExtractor->setInput( new FileInput( __DIR__ . "/../../../samples/data/json/colors.json" ) );
        $jsonExtractor->extract();
    }

    /**
     * @throws SourceWatcherException
     */
    public function testNoFileInputProvided () : void
    {
        $this->expectException( SourceWatcherException::class );

        $jsonExtractor = new JsonExtractor();
        $jsonExtractor->setColumns( [ $this->colorIndex => "some.selector" ] );
        $jsonExtractor->setInput( $this->createMock( Input::class ) );
        $jsonExtractor->extract();
    }
}
