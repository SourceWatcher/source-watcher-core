<?php declare( strict_types=1 );

namespace Coco\SourceWatcher\Tests\Core\IO\Inputs;

use Coco\SourceWatcher\Core\IO\Inputs\ApiInput;
use PHPUnit\Framework\TestCase;

class ApiInputTest extends TestCase
{
    public function testConstructorWithDefaults () : void
    {
        $input = new ApiInput();
        $this->assertNull( $input->getInput() );
        $this->assertSame( 10, $input->getTimeout() );
        $this->assertSame( [], $input->getHeaders() );
    }

    public function testConstructorWithArguments () : void
    {
        $url = "https://api.example.com/resource";
        $timeout = 5;
        $headers = [ "X-Custom" => "value" ];
        $input = new ApiInput( $url, $timeout, $headers );
        $this->assertSame( $url, $input->getInput() );
        $this->assertSame( $timeout, $input->getTimeout() );
        $this->assertSame( $headers, $input->getHeaders() );
    }

    public function testSetInputAndGetInput () : void
    {
        $input = new ApiInput();
        $input->setInput( "https://example.com" );
        $this->assertSame( "https://example.com", $input->getInput() );
    }

    public function testSetInputAcceptsNull () : void
    {
        $input = new ApiInput( "https://example.com" );
        $input->setInput( null );
        $this->assertNull( $input->getInput() );
    }

    public function testSetTimeoutAndGetTimeout () : void
    {
        $input = new ApiInput( null, 10 );
        $this->assertSame( 10, $input->getTimeout() );
        $input->setTimeout( 30 );
        $this->assertSame( 30, $input->getTimeout() );
    }

    public function testSetHeadersAndGetHeaders () : void
    {
        $input = new ApiInput();
        $this->assertSame( [], $input->getHeaders() );
        $headers = [ "Authorization" => "Bearer token", "Accept" => "application/json" ];
        $input->setHeaders( $headers );
        $this->assertSame( $headers, $input->getHeaders() );
    }
}
