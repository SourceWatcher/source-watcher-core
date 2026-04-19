<?php declare( strict_types=1 );

namespace Coco\SourceWatcher\Tests\Core\IO\Outputs;

use Coco\SourceWatcher\Core\Database\Connections\Connector;
use Coco\SourceWatcher\Core\IO\Outputs\DatabaseOutput;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

/**
 * Class DatabaseOutputTests
 *
 * @package Coco\SourceWatcher\Tests\Core\IO\Outputs
 */
#[AllowMockObjectsWithoutExpectations]
class DatabaseOutputTests extends TestCase
{
    public function testSetGetOutput () : void
    {
        $databaseOutput = new DatabaseOutput();

        $givenOutput = $this->createMock( Connector::class );
        $databaseOutput->setOutput( $givenOutput );

        $output = $databaseOutput->getOutput();
        $this->assertCount( 1, $output );
        $this->assertSame( $givenOutput, $output[0] );
    }

    public function testConstructorWithExtraConnectors () : void
    {
        $primary = $this->createMock( Connector::class );
        $extra = $this->createMock( Connector::class );
        $databaseOutput = new DatabaseOutput( $primary, $extra );

        $output = $databaseOutput->getOutput();
        $this->assertCount( 2, $output );
        $this->assertSame( $primary, $output[0] );
        $this->assertSame( $extra, $output[1] );
    }
}
