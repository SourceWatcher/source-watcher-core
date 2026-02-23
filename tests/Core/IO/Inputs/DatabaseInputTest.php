<?php declare( strict_types=1 );

namespace Coco\SourceWatcher\Tests\Core\IO\Inputs;

use Coco\SourceWatcher\Core\Database\Connections\MySqlConnector;
use Coco\SourceWatcher\Core\IO\Inputs\DatabaseInput;
use Coco\SourceWatcher\Core\IO\Inputs\FileInput;
use PHPUnit\Framework\TestCase;

class DatabaseInputTest extends TestCase
{
    public function testSetInputWithNonConnectorStoresNull () : void
    {
        $connector = new MySqlConnector();
        $input = new DatabaseInput( $connector );
        $input->setInput( new FileInput( "/tmp/foo" ) );
        $this->assertNull( $input->getInput() );
    }
}
