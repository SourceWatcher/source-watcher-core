<?php declare( strict_types=1 );

namespace Coco\SourceWatcher\Tests\Core\Api;

use Coco\SourceWatcher\Core\Api\ApiReader;
use Coco\SourceWatcher\Core\Exception\SourceWatcherException;
use Coco\SourceWatcher\Utils\Internationalization;
use PHPUnit\Framework\TestCase;

/**
 * Class ApiReaderTest
 *
 * @package Coco\SourceWatcher\Tests\Core\Api
 */
class ApiReaderTest extends TestCase
{
    private string $github_emojis_api;

    protected function setUp () : void
    {
        $this->github_emojis_api = "https://api.github.com/emojis";
    }

    public function testSetGetResourceURL () : void
    {
        $apiReader = new ApiReader();

        $givenResourceURL = $this->github_emojis_api;
        $expectedResourceURL = $this->github_emojis_api;

        $apiReader->setResourceURL( $givenResourceURL );

        $this->assertEquals( $expectedResourceURL, $apiReader->getResourceURL() );
    }

    public function testSetGetTimeout () : void
    {
        $apiReader = new ApiReader();

        $givenTimeout = 10;
        $expectedTimeout = 10;

        $apiReader->setTimeout( $givenTimeout );

        $this->assertEquals( $expectedTimeout, $apiReader->getTimeout() );
    }

    public function testSetGetHeaders () : void
    {
        $apiReader = new ApiReader();

        $givenHeaders = [
            "Cache-Control: no-cache",
            "Content-Type: application/x-www-form-urlencoded; charset=utf-8",
            "Host: www.example.com"
        ];
        $expectedHeaders = [
            "Cache-Control: no-cache",
            "Content-Type: application/x-www-form-urlencoded; charset=utf-8",
            "Host: www.example.com"
        ];

        $apiReader->setHeaders( $givenHeaders );

        $this->assertEquals( $expectedHeaders, $apiReader->getHeaders() );
    }

    /**
     * @throws SourceWatcherException
     */
    public function testGetExceptionFromNoResourceURL () : void
    {
        $apiReader = new ApiReader();

        $this->expectException( SourceWatcherException::class );
        $this->expectExceptionMessage( Internationalization::getInstance()->getText( ApiReader::class,
            "No_Resource_URL_Found" ) );

        $apiReader->read();
    }

    /**
     * @throws SourceWatcherException
     */
    public function testGetResult () : void
    {
        $apiReader = new ApiReader();
        $apiReader->setResourceURL( "https://api.github.com/emojis" );
        $apiReader->setHeaders( [ "User-Agent: request" ] );

        $this->assertNotNull( $apiReader->read() );
    }

    /**
     * @throws SourceWatcherException
     */
    public function testAttemptsLoop () : void
    {
        $apiReader = new ApiReader();
        $apiReader->setResourceURL( "https://thiswebsiteisnotreal.com" );

        $this->assertNotNull( $apiReader->read() );
    }
}
