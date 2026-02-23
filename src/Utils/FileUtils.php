<?php

namespace Coco\SourceWatcher\Utils;

/**
 * Class FileUtils
 *
 * @package Coco\SourceWatcher\Utils
 */
class FileUtils
{
    public static function file_build_path ( ...$segments ) : string
    {
        return join( DIRECTORY_SEPARATOR, $segments );
    }

    public static function getUserHomePath () : string
    {
        $home = getenv( "HOME" );
        if ( $home !== false && $home !== "" ) {
            return $home;
        }
        if ( !empty( $_SERVER["HOMEDRIVE"] ) && !empty( $_SERVER["HOMEPATH"] ) ) {
            return $_SERVER["HOMEDRIVE"] . $_SERVER["HOMEPATH"];
        }
        return "";
    }
}
