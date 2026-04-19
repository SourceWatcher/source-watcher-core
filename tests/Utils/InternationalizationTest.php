<?php declare( strict_types=1 );

namespace Coco\SourceWatcher\Tests\Utils;

use Coco\SourceWatcher\Utils\Internationalization;
use PHPUnit\Framework\TestCase;

/**
 * Class InternationalizationTest
 *
 * @package Coco\SourceWatcher\Tests\Utils
 */
class InternationalizationTest extends TestCase
{
    private string $envFileLocation = __DIR__ . "/../../" . ".env";

    private bool $preExistingEnvFile = false;

    private string $temporaryNameForEnvFile = "";

    private bool $envFileRenamed = false;

    private array $envConditionsPerFunction = [
        "testGetInstance" => [ "add_i18n_property" => true ], // #NOSONAR
        "testGetText" => [ "add_i18n_property" => true ],
        "testNoEnvFileDefaultToEnglish" => [ "add_i18n_property" => false ]
    ];

    public function setUp () : void
    {
        $nextFunctionToBeCalled = $this->name();

        if ( file_exists( $this->envFileLocation ) ) {
            $this->preExistingEnvFile = true;

            $this->temporaryNameForEnvFile = sprintf( "%s-%s", $this->envFileLocation, time() );

            if ( rename( $this->envFileLocation, $this->temporaryNameForEnvFile ) ) {
                $this->envFileRenamed = true;

                unset( $_ENV["I18N_LANGUAGE"] );
            }
        } else {
            $addi18nProperty = $this->envConditionsPerFunction[$nextFunctionToBeCalled]["add_i18n_property"];

            $envFile = fopen( $this->envFileLocation, "w" );

            if ( $addi18nProperty ) {
                $i18nSetting = "I18N_LANGUAGE=en_US";
                fwrite( $envFile, $i18nSetting );
            }

            fclose( $envFile );
        }
    }

    public function tearDown () : void
    {
        if ( !$this->preExistingEnvFile ) {
            unlink( $this->envFileLocation );
        } else {
            if ( $this->envFileRenamed ) {
                rename( $this->temporaryNameForEnvFile, $this->envFileLocation );
            }
        }
    }

    public function testGetInstance () : void
    {
        $this->assertEquals( new Internationalization(), Internationalization::getInstance() );
    }

    public function testGetText () : void
    {
        $expectedString = "This is a test entry!";
        $actualString = Internationalization::getInstance()->getText( InternationalizationTest::class,
            "This_Is_A_Test_Entry" );

        $this->assertNotNull( $actualString );
        $this->assertEquals( $expectedString, $actualString );
    }

    public function testNoEnvFileDefaultToEnglish () : void // #NOSONAR
    {
        $expectedString = "This is a test entry!";
        $actualString = Internationalization::getInstance()->getText( InternationalizationTest::class,
            "This_Is_A_Test_Entry" );

        $this->assertNotNull( $actualString );
        $this->assertEquals( $expectedString, $actualString );
    }
}
