<?php declare( strict_types=1 );

namespace Coco\SourceWatcher\Tests\Core;

use Coco\SourceWatcher\Core\Data\Row;
use PHPUnit\Framework\TestCase;

/**
 * Class RowTest
 *
 * @package Coco\SourceWatcher\Tests\Core
 */
class RowTest extends TestCase
{
    private string $janeDoe;

    public function setUp () : void
    {
        $this->janeDoe = "Jane Doe";
    }

    public function testGetAndSetAttributes () : void
    {
        $givenAttributes = [ "id", "name" ];

        $row = new Row( [] );
        $row->setAttributes( $givenAttributes );

        $expectedAttributes = [ "id", "name" ];
        $actualAttributes = $row->getAttributes();

        $this->assertEquals( $expectedAttributes, $actualAttributes );
    }

    public function testOffsetExistsViaEmpty () : void
    {
        $row = new Row( [ "name" => $this->janeDoe ] );

        // test the offsetExists method
        $this->assertTrue( empty( $row["id"] ) );
    }

    public function testOffsetExistsViaIsSet () : void
    {
        $row = new Row( [ "name" => $this->janeDoe ] );

        // test the offsetExists method
        $this->assertNotTrue( isset( $row["id"] ) );
    }

    public function testOffsetGetAndSetWithArrayNotation () : void
    {
        $givenName = $this->janeDoe;

        $row = new Row( [ "id" => 1 ] );
        $row["name"] = $givenName;  // test the offsetSet method

        $expectedName = $this->janeDoe;
        $actualName = $row["name"]; // test the offsetGet method

        $this->assertEquals( $expectedName, $actualName );
    }

    public function testOffsetUnset () : void
    {
        $row = new Row( [ "id" => 1, "name" => $this->janeDoe ] );

        // test the offsetUnset method
        unset( $row["id"] );

        $expectedRow = new Row( [ "name" => $this->janeDoe ] );

        $this->assertEquals( $expectedRow, $row );
    }

    public function testGetAndSetAttribute () : void
    {
        $givenName = $this->janeDoe;

        $row = new Row( [ "id" => 1 ] );
        $row->set( "name", $givenName );    // test the set method

        $expectedName = $this->janeDoe;
        $actualName = $row->get( "name" );  // test the get method

        $this->assertEquals( $expectedName, $actualName );
    }

    public function testRemoveAttribute () : void
    {
        $row = new Row( [ "id" => 1, "name" => $this->janeDoe ] );

        // test the remove method
        $row->remove( "id" );

        $expectedRow = new Row( [ "name" => $this->janeDoe ] );

        $this->assertEquals( $expectedRow, $row );
    }

    public function testGetAndSetWithObjectNotation () : void
    {
        $givenName = $this->janeDoe;

        $row = new Row( [ "id" => 1 ] );
        $row->name = $givenName;    // test the __set method

        $expectedName = $this->janeDoe;
        $actualName = $row->name;   // test the __get method

        $this->assertEquals( $expectedName, $actualName );
    }
}
