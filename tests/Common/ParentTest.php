<?php declare( strict_types=1 );

namespace Coco\SourceWatcher\Tests\Common;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use PHPUnit\Framework\TestCase;

/**
 * Class ParentTest
 *
 * @package Coco\SourceWatcher\Tests\Common
 */
class ParentTest extends TestCase
{
    protected function getEnvironmentVariable ( string $variableName, $default, $castingFunctionName = null )
    {
        $keyExists = array_key_exists( $variableName, $_ENV );
        $value = null;

        if ( $keyExists ) {
            $value = $_ENV[$variableName];
        } else {
            try {
                $dotenv = Dotenv::createImmutable( __DIR__ . "/../../" );
                $dotenv->load();
            } catch ( InvalidPathException $e ) {
                // .env missing (e.g. in CI/Docker); use default when var not in $_ENV
            }

            $value = getenv( $variableName ) ?: $_ENV[$variableName] ?? null;

            if ( empty( $value ) ) {
                $value = $default;
            }
        }

        if ( !empty( $castingFunctionName ) ) {
            return call_user_func( $castingFunctionName, $value );
        }

        return $value;
    }

    public function testCanGetDefaultValueForEnvVar () : void
    {
        $variableName = "SOMETHING_NOT_SETUP_IN_ENV";
        $default = "a default value";
        $result = $this->getEnvironmentVariable( $variableName, $default );
        $expected = "a default value";

        $this->assertEquals( $expected, $result );
    }
}
