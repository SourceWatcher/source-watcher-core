<?php

namespace Coco\SourceWatcher\Tests\Core\Transformers;

use Coco\SourceWatcher\Core\Data\Row;
use Coco\SourceWatcher\Core\Transformers\GuessGenderTransformer;
use GenderDetector\Country;
use GenderDetector\Gender;
use PHPUnit\Framework\TestCase;

class GuessGenderTransformerTest extends TestCase
{
    public function testGuessGender1(): void {
        $transformer = new GuessGenderTransformer();
        $transformer->options( ['country' => Country::USA, 'firstNameField' => 'first_name', 'genderField' => 'gender'] );

        $row = new Row( [ 'first_name' => 'Jean', 'gender' => null ] );

        $transformer->transform( $row );

        $this->assertNotNull( $row->get( 'gender' ) );
        $this->assertNotEmpty( $row->get( 'gender' ) );
        $this->assertEquals( Gender::MOSTLY_FEMALE, $row->get( 'gender' ) );
    }

    public function testGuessGender2(): void {
        $transformer = new GuessGenderTransformer();
        $transformer->options( ['country' => Country::USA, 'firstNameField' => 'first_name', 'genderField' => 'gender'] );

        $row = new Row( [ 'first_name' => 'Paul', 'gender' => null ] );

        $transformer->transform( $row );

        $this->assertNotNull( $row->get( 'gender' ) );
        $this->assertNotEmpty( $row->get( 'gender' ) );
        $this->assertEquals( Gender::MALE, $row->get( 'gender' ) );
    }

    public function testGuessGender3(): void {
        $transformer = new GuessGenderTransformer();
        $transformer->options( ['country' => Country::USA, 'firstNameField' => 'first_name', 'genderField' => 'gender'] );

        $row = new Row( [ 'first_name' => 'jean paul', 'gender' => null ] );

        $transformer->transform( $row );

        $this->assertNotNull( $row->get( 'gender' ) );
        $this->assertNotEmpty( $row->get( 'gender' ) );
        $this->assertEquals( Gender::MALE, $row->get( 'gender' ) );
    }
}
