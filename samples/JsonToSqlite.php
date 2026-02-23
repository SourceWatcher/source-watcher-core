<?php

/**
 * Sample: JSON file → SQLite.
 * Extracts color names from samples/data/json/colors.json and loads into SQLite.
 * Creates samples/data/sqlite/colors-out.sqlite and table "colors" if needed.
 */
require_once __DIR__ . "/bootstrap.php";

use Coco\SourceWatcher\Core\Database\Connections\SqliteConnector;
use Coco\SourceWatcher\Core\IO\Inputs\FileInput;
use Coco\SourceWatcher\Core\IO\Outputs\DatabaseOutput;
use Coco\SourceWatcher\Core\SourceWatcher;
use Coco\SourceWatcher\Core\SourceWatcherException;

$sqlitePath = __DIR__ . "/data/sqlite/colors-out.sqlite";
$dataDir   = dirname( $sqlitePath );

if ( !is_dir( $dataDir ) ) {
    mkdir( $dataDir, 0755, true );
}

$pdo = new PDO( "sqlite:" . $sqlitePath );
$pdo->exec( "CREATE TABLE IF NOT EXISTS colors (color TEXT)" );

$connector = new SqliteConnector();
$connector->setPath( $sqlitePath );
$connector->setTableName( "colors" );

$sourceWatcher = new SourceWatcher();

try {
    $sourceWatcher
        ->extract( "Json", new FileInput( __DIR__ . "/data/json/colors.json" ), [
            "columns" => [ "color" => "colors.*.color" ]
        ] )
        ->load( "Database", new DatabaseOutput( $connector ) )
        ->run();
} catch ( SourceWatcherException $exception ) {
    echo sprintf( "Something unexpected went wrong: %s", $exception->getMessage() );
}
