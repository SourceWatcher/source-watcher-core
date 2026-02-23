<?php

namespace Coco\SourceWatcher\Tests\Core\Transformers;

use Coco\SourceWatcher\Core\Data\Row;
use Coco\SourceWatcher\Core\Transformers\ConvertCaseTransformer;
use PHPUnit\Framework\TestCase;

/**
 * Class ConvertCaseTransformerTest
 *
 * @package Coco\SourceWatcher\Tests\Core\Transformers
 */
class ConvertCaseTransformerTest extends TestCase
{
    public string $columnsIndex;

    public string $encodingIndex;
    public string $encodingUTF_8;

    public string $johnDoe;

    public function setUp () : void
    {
        $this->columnsIndex = "columns";

        $this->encodingIndex = "encoding";
        $this->encodingUTF_8 = "UTF-8";

        $this->johnDoe = "John Doe";
    }

    public function testCaseModeUpperConstant () : void
    {
        $expectedValue = MB_CASE_UPPER;
        $actualValue = ConvertCaseTransformer::CONVERT_CASE_MODE_UPPER;

        $this->assertEquals( $expectedValue, $actualValue );
    }

    public function testCaseModeLowerConstant () : void
    {
        $expectedValue = MB_CASE_LOWER;
        $actualValue = ConvertCaseTransformer::CONVERT_CASE_MODE_LOWER;

        $this->assertEquals( $expectedValue, $actualValue );
    }

    public function testCaseModeTitleConstant () : void
    {
        $expectedValue = MB_CASE_TITLE;
        $actualValue = ConvertCaseTransformer::CONVERT_CASE_MODE_TITLE;

        $this->assertEquals( $expectedValue, $actualValue );
    }

    public function testTransformRowModeUpperAllColumns () : void
    {
        $transformer = new ConvertCaseTransformer();
        $transformer->options( [
            $this->columnsIndex => [ "id", "name" ],
            $this->encodingIndex => $this->encodingUTF_8,
            "mode" => ConvertCaseTransformer::CONVERT_CASE_MODE_UPPER
        ] );

        $givenRow = new Row( [ "id" => 1, "name" => $this->johnDoe ] );

        $transformer->transform( $givenRow );

        $expectedRow = new Row( [ "ID" => 1, "NAME" => $this->johnDoe ] );

        $this->assertEquals( $expectedRow, $givenRow );
    }

    public function testTransformRowModeUpperSomeColumns () : void
    {
        $transformer = new ConvertCaseTransformer();
        $transformer->options( [
            $this->columnsIndex => [ "id" ],
            $this->encodingIndex => $this->encodingUTF_8,
            "mode" => ConvertCaseTransformer::CONVERT_CASE_MODE_UPPER
        ] );

        $givenRow = new Row( [ "id" => 1, "name" => $this->johnDoe ] );

        $transformer->transform( $givenRow );

        $expectedRow = new Row( [ "ID" => 1, "name" => $this->johnDoe ] );

        $this->assertEquals( $expectedRow, $givenRow );
    }

    public function testTransformRowModeLowerAllColumns () : void
    {
        $transformer = new ConvertCaseTransformer();
        $transformer->options( [
            $this->columnsIndex => [ "ID", "NAME" ],
            $this->encodingIndex => $this->encodingUTF_8,
            "mode" => ConvertCaseTransformer::CONVERT_CASE_MODE_LOWER
        ] );

        $givenRow = new Row( [ "ID" => 1, "NAME" => $this->johnDoe ] );

        $transformer->transform( $givenRow );

        $expectedRow = new Row( [ "id" => 1, "name" => $this->johnDoe ] );

        $this->assertEquals( $expectedRow, $givenRow );
    }

    public function testTransformRowModeLowerSomeColumns () : void
    {
        $transformer = new ConvertCaseTransformer();
        $transformer->options( [
            $this->columnsIndex => [ "ID" ],
            $this->encodingIndex => $this->encodingUTF_8,
            "mode" => ConvertCaseTransformer::CONVERT_CASE_MODE_LOWER
        ] );

        $givenRow = new Row( [ "ID" => 1, "NAME" => $this->johnDoe ] );

        $transformer->transform( $givenRow );

        $expectedRow = new Row( [ "id" => 1, "NAME" => $this->johnDoe ] );

        $this->assertEquals( $expectedRow, $givenRow );
    }

    public function testTransformRowModeTitleAllColumns () : void
    {
        $transformer = new ConvertCaseTransformer();
        $transformer->options( [
            $this->columnsIndex => [ "id", "name" ],
            $this->encodingIndex => $this->encodingUTF_8,
            "mode" => ConvertCaseTransformer::CONVERT_CASE_MODE_TITLE
        ] );

        $givenRow = new Row( [ "id" => 1, "name" => $this->johnDoe ] );

        $transformer->transform( $givenRow );

        $expectedRow = new Row( [ "Id" => 1, "Name" => $this->johnDoe ] );

        $this->assertEquals( $expectedRow, $givenRow );
    }

    public function testTransformRowModeTitleSomeColumns () : void
    {
        $transformer = new ConvertCaseTransformer();
        $transformer->options( [
            $this->columnsIndex => [ "id" ],
            $this->encodingIndex => $this->encodingUTF_8,
            "mode" => ConvertCaseTransformer::CONVERT_CASE_MODE_TITLE
        ] );

        $givenRow = new Row( [ "id" => 1, "name" => $this->johnDoe ] );

        $transformer->transform( $givenRow );

        $expectedRow = new Row( [ "Id" => 1, "name" => $this->johnDoe ] );

        $this->assertEquals( $expectedRow, $givenRow );
    }
}
