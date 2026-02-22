<?php declare( strict_types=1 );

namespace Coco\SourceWatcher\Tests\Vendors\Virology\COVID_19\Florida;

use Coco\SourceWatcher\Core\Api\ApiReader;
use Coco\SourceWatcher\Core\SourceWatcherException;
use Coco\SourceWatcher\Vendors\Virology\COVID_19\Florida\Covid19FloridaApiReader;
use PHPUnit\Framework\TestCase;

/**
 * Class Covid19FloridaApiReaderTest
 *
 * @package Coco\SourceWatcher\Tests\Vendors\Virology\COVID_19\Florida
 */
class Covid19FloridaApiReaderTest extends TestCase
{
    public string $floridaCOVID19CasesURL;

    public string $genericQueryParameters;

    public array $statisticFields = [];

    public function setUp () : void
    {
        $this->floridaCOVID19CasesURL = "https://services1.arcgis.com/CY1LXxl9zlJeBuRZ/arcgis/rest/services/Florida_COVID19_Cases/FeatureServer/0/query?";

        $this->genericQueryParameters = 'f=json&where=1=1&returnGeometry=false&spatialRel=esriSpatialRelIntersects&outFields=*&outStatistics=[{"statisticType":"sum","onStatisticField":"%s","outStatisticFieldName":"value"}]&cacheHint=true';

        $this->statisticFields = [
            "T_positive" => "Total Positive Cases",

            "T_NegRes" => "Total Negative Florida Residents",
            "T_NegNotFLRes" => "Total Negative Non-Florida Residents",

            "T_total" => "Total Number Of Tests",

            "Deaths" => "Deaths",

            "C_FLRes" => "Positive Florida Residents",
            "C_FLResOut" => "Positive Non-Florida Residents",

            "C_HospYes_Res" => "Hospitalizations Florida Residents",
            "C_HospYes_NonRes" => "Hospitalizations Non-Florida Residents"
        ];
    }

    public function testConstructor () : void
    {
        $reader = new Covid19FloridaApiReader();
        $this->assertNotNull( $reader );
        $this->assertInstanceOf( Covid19FloridaApiReader::class, $reader );
        $this->assertInstanceOf( ApiReader::class, $reader );
    }

    public function testSetGetFloridaCOVID19CasesURL () : void
    {
        $reader = new Covid19FloridaApiReader();

        $givenFloridaCOVID19CasesURL = $this->floridaCOVID19CasesURL;
        $expectedFloridaCOVID19CasesURL = $this->floridaCOVID19CasesURL;

        $reader->setFloridaCOVID19CasesURL( $givenFloridaCOVID19CasesURL );

        $this->assertNotNull( $reader->getFloridaCOVID19CasesURL() );
        $this->assertEquals( $expectedFloridaCOVID19CasesURL, $reader->getFloridaCOVID19CasesURL() );
    }

    public function testSetGetGenericQueryParameters () : void
    {
        $reader = new Covid19FloridaApiReader();

        $givenGenericQueryParameters = $this->genericQueryParameters;
        $expectedGenericQueryParameters = $this->genericQueryParameters;

        $reader->setGenericQueryParameters( $givenGenericQueryParameters );

        $this->assertNotNull( $reader->getGenericQueryParameters() );
        $this->assertEquals( $expectedGenericQueryParameters, $reader->getGenericQueryParameters() );
    }

    public function testSetGetStatisticFields () : void
    {
        $reader = new Covid19FloridaApiReader();

        $givenStatisticFields = $this->statisticFields;
        $expectedStatisticFields = $this->statisticFields;

        $reader->setStatisticFields( $givenStatisticFields );

        $this->assertNotNull( $reader->getStatisticFields() );
        $this->assertEquals( $expectedStatisticFields, $reader->getStatisticFields() );
    }

    /**
     * getResultFromURL returns "0" when API response decodes to null (covers null branch)
     */
    public function testGetResultFromURLReturnsZeroWhenJsonNull () : void
    {
        $reader = new class extends Covid19FloridaApiReader {
            public function read () : string
            {
                return "null";
            }
        };
        $result = $reader->getResultFromURL( "https://example.com/any" );
        $this->assertSame( "0", $result );
    }

    /**
     * @throws SourceWatcherException
     */
    public function testGetExceptionFromIncorrectURL () : void
    {
        $this->expectException( SourceWatcherException::class );

        $reader = new Covid19FloridaApiReader();
        $reader->setHeaders( [ "User-Agent: request" ] );

        $reader->getResultFromURL( "https://api.github.com/emojis" );
    }

    public function testGetNonExistentField () : void
    {
        $reader = new Covid19FloridaApiReader();

        $otherStatisticFields = [ "SomeFakeField" => "With some random description" ];
        $reader->setStatisticFields( $otherStatisticFields );

        $results = $reader->getResults();
        $this->assertNotNull( $results );

        $this->assertArrayHasKey( "SomeFakeField", $results );
    }

    public function testGetResults () : void
    {
        $reader = new Covid19FloridaApiReader();
        $results = $reader->getResults();

        $this->assertNotNull( $results );

        foreach ( $this->statisticFields as $key => $value ) {
            $this->assertArrayHasKey( $key, $results );
        }
    }
}
