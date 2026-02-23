<?php declare( strict_types=1 );

namespace Coco\SourceWatcher\Tests\Watcher\Handler;

use Coco\SourceWatcher\Core\Exception\SourceWatcherException;
use Coco\SourceWatcher\Watcher\Handler\WebPageHandler;
use PHPUnit\Framework\TestCase;

/**
 * Class WebPageHandlerTest
 *
 * @package Coco\SourceWatcher\Tests\Watcher\Handler
 */
class WebPageHandlerTest extends TestCase
{
    public string $java_florida_jobs_url;
    public string $php_florida_jobs_url;

    public function setUp () : void
    {
        $this->java_florida_jobs_url = "https://stackoverflow.com/jobs?q=Java&l=Florida+USA&d=100&u=Miles";
        $this->php_florida_jobs_url = "https://stackoverflow.com/jobs?q=PHP&l=Florida+USA&d=100&u=Miles";
    }

    public function testSetGetURL () : void
    {
        $givenURL = $this->java_florida_jobs_url;
        $expectedURL = $this->java_florida_jobs_url;

        $webPageHandler = new WebPageHandler( $givenURL );

        $this->assertNotNull( $webPageHandler->getUrl() );
        $this->assertEquals( $expectedURL, $webPageHandler->getUrl() );

        $givenURL = $this->php_florida_jobs_url;
        $expectedURL = $this->php_florida_jobs_url;

        $webPageHandler->setUrl( $givenURL );

        $this->assertNotNull( $webPageHandler->getUrl() );
        $this->assertEquals( $expectedURL, $webPageHandler->getUrl() );
    }

    /**
     * @throws SourceWatcherException
     */
    public function testReadWithNoURL () : void
    {
        $this->expectException( SourceWatcherException::class );

        $webPageHandler = new WebPageHandler( "" );
        $webPageHandler->read();
    }

    /**
     * @throws SourceWatcherException
     */
    public function testReadWithBadURL () : void
    {
        $webPageHandler = new WebPageHandler( "this is not a URL" );
        $webPageHandler->read();

        $this->assertEmpty( $webPageHandler->getHtml() );

        $this->assertNotEmpty( $webPageHandler->getDom() );
        $this->assertEquals( 0, $webPageHandler->getDom()->childNodes->length );
    }

    /**
     * @throws SourceWatcherException
     */
    public function testReadWithGoodURL () : void
    {
        $webPageHandler = new WebPageHandler( "https://stackoverflow.com/jobs?q=Java&l=Florida+USA&d=100&u=Miles" );
        $webPageHandler->read();

        $this->assertNotEmpty( $webPageHandler->getHtml() );

        $this->assertNotEmpty( $webPageHandler->getDom() );
        $this->assertGreaterThan( 0, $webPageHandler->getDom()->childNodes->length );
    }
}
