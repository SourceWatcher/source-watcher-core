<?php declare( strict_types=1 );

namespace Coco\SourceWatcher\Tests\Core;

use Coco\SourceWatcher\Core\Exception\SourceWatcherException;
use PHPUnit\Framework\TestCase;

/**
 * Class SourceWatcherExceptionTest
 *
 * @package Coco\SourceWatcher\Tests\Core
 */
class SourceWatcherExceptionTest extends TestCase
{
    public function testExceptionMessageAndCode () : void
    {
        $message = "Something went wrong";
        $code = 42;
        $e = new SourceWatcherException( $message, $code );

        $this->assertSame( $message, $e->getMessage() );
        $this->assertSame( $code, $e->getCode() );
    }

    public function testExceptionCanBeThrownAndCaught () : void
    {
        $message = "Test error";
        $caught = null;

        try {
            throw new SourceWatcherException( $message );
        } catch ( SourceWatcherException $e ) {
            $caught = $e;
        }

        $this->assertInstanceOf( SourceWatcherException::class, $caught );
        $this->assertSame( $message, $caught->getMessage() );
    }
}
