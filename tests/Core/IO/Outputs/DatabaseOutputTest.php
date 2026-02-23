<?php declare( strict_types=1 );

namespace Coco\SourceWatcher\Tests\Core\IO\Outputs;

use Coco\SourceWatcher\Core\Database\Connections\Connector;
use Coco\SourceWatcher\Core\IO\Outputs\DatabaseOutput;
use PHPUnit\Framework\TestCase;

class DatabaseOutputTest extends TestCase
{
    public function testConstructorWithNull () : void
    {
        $output = new DatabaseOutput();
        $result = $output->getOutput();
        $this->assertIsArray( $result );
        $this->assertCount( 1, $result );
        $this->assertNull( $result[0] );
    }

    public function testConstructorWithConnector () : void
    {
        $connector = $this->createMock( Connector::class );
        $output = new DatabaseOutput( $connector );
        $result = $output->getOutput();
        $this->assertCount( 1, $result );
        $this->assertSame( $connector, $result[0] );
    }

    public function testConstructorWithExtraConnectors () : void
    {
        $conn1 = $this->createMock( Connector::class );
        $conn2 = $this->createMock( Connector::class );
        $output = new DatabaseOutput( $conn1, $conn2 );
        $result = $output->getOutput();
        $this->assertCount( 2, $result );
        $this->assertSame( $conn1, $result[0] );
        $this->assertSame( $conn2, $result[1] );
    }

    public function testSetOutputAndGetOutput () : void
    {
        $connector = $this->createMock( Connector::class );
        $output = new DatabaseOutput();
        $output->setOutput( $connector );
        $result = $output->getOutput();
        $this->assertCount( 1, $result );
        $this->assertSame( $connector, $result[0] );
    }
}
