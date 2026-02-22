<?php

namespace Coco\SourceWatcher\Utils;

use Dotenv\Dotenv;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Internationalization
 *
 * @package Coco\SourceWatcher\Utils
 */
class Internationalization
{
    private static ?Internationalization $instance = null;

    public static string $I18N_LANGUAGE_INDEX = "I18N_LANGUAGE";

    public static function getInstance () : Internationalization
    {
        if ( is_null( static::$instance ) ) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    private function getLanguageFromEnvFile () : ?string
    {
        $language = array_key_exists( Internationalization::$I18N_LANGUAGE_INDEX,
            $_ENV ) ? $_ENV[Internationalization::$I18N_LANGUAGE_INDEX] : null;

        if ( empty( $language ) ) {
            $envFileDirectory = __DIR__ . "/../../";
            $envFileLocation = $envFileDirectory . ".env";

            if ( file_exists( $envFileLocation ) ) {
                $dotEnv = Dotenv::createImmutable( $envFileDirectory );
                $dotEnv->load();

                $language = array_key_exists( Internationalization::$I18N_LANGUAGE_INDEX,
                    $_ENV ) ? $_ENV[Internationalization::$I18N_LANGUAGE_INDEX] : null;
            }
        }

        return $language;
    }

    public function getText ( string $className, string $entry, ?string $language = null ) : string
    {
        if ( empty( $language ) ) {
            $language = $this->getLanguageFromEnvFile();
        }

        if ( empty( $language ) ) {
            $language = "en_US";
        }

        $data = Yaml::parseFile( __DIR__ . sprintf( "/../../resources/Locales/translations_%s.yml", $language ) );

        $classPathParts = explode( "\\", $className );

        $dataCopy = $data;

        foreach ( $classPathParts as $currentPart ) {
            $dataCopy = $dataCopy[$currentPart];
        }

        return $dataCopy[$entry];
    }
}
