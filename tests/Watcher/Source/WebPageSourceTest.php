<?php declare( strict_types=1 );

namespace Coco\SourceWatcher\Tests\Watcher\Source;

use Coco\SourceWatcher\Watcher\Handler\WebPageHandler;
use Coco\SourceWatcher\Watcher\Source\WebPageSource;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

/**
 * Class WebPageSourceTest
 *
 * @package Coco\SourceWatcher\Tests\Watcher\Source
 */
#[AllowMockObjectsWithoutExpectations]
class WebPageSourceTest extends TestCase
{
    public string $localhost_url;

    public function setUp () : void
    {
        $this->localhost_url = "http://localhost/";
    }

    public function testSetGetUrl () : void
    {
        $givenURL = $this->localhost_url;
        $expectedURL = $this->localhost_url;

        $webPageSource = new WebPageSource( $givenURL );

        $this->assertNotNull( $webPageSource->getUrl() );
        $this->assertNotEmpty( $webPageSource->getUrl() );
        $this->assertEquals( $expectedURL, $webPageSource->getUrl() );


        $webPageSource->setUrl( $givenURL );

        $this->assertNotNull( $webPageSource->getUrl() );
        $this->assertNotEmpty( $webPageSource->getUrl() );
        $this->assertEquals( $expectedURL, $webPageSource->getUrl() );
    }

    public function testSetGetHandler () : void
    {
        $webPageSource = new WebPageSource( $this->localhost_url );

        $givenHandler = $this->createMock( WebPageHandler::class );
        $expectedHandler = $this->createMock( WebPageHandler::class );

        $webPageSource->setHandler( $givenHandler );

        $this->assertNotNull( $webPageSource->getHandler() );
        $this->assertEquals( $expectedHandler, $webPageSource->getHandler() );
    }
}
