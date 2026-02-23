<?php

/**
 * Full ETL (XML): API → Transform → SQLite.
 * Extract: GET an XML endpoint via ApiExtractor with "response_type" => "xml".
 * Rows are built from the root's child elements (e.g. each <food> → one row).
 * Transform: RenameColumns (description → description_text).
 * Load: SQLite table "food" (name, price, description_text, calories).
 *
 * Uses a public XML sample (W3Schools). Run from repo root.
 */
require_once __DIR__ . "/bootstrap.php";

use Coco\SourceWatcher\Core\Database\Connections\SqliteConnector;
use Coco\SourceWatcher\Core\IO\Inputs\ApiInput;
use Coco\SourceWatcher\Core\IO\Outputs\DatabaseOutput;
use Coco\SourceWatcher\Core\Exception\SourceWatcherException;
use Coco\SourceWatcher\Core\Pipeline\SourceWatcher;

$sqlitePath = __DIR__ . "/data/sqlite/xml-menu.sqlite";
$dataDir = dirname( $sqlitePath );
if ( !is_dir( $dataDir ) ) {
    mkdir( $dataDir, 0755, true );
}

$pdo = new PDO( "sqlite:" . $sqlitePath );
$pdo->exec( "CREATE TABLE IF NOT EXISTS food (name TEXT, price TEXT, description_text TEXT, calories TEXT)" );

$connector = new SqliteConnector();
$connector->setPath( $sqlitePath );
$connector->setTableName( "food" );

$sourceWatcher = new SourceWatcher();

$xmlUrl = "https://www.w3schools.com/xml/simple.xml";

try {
    $sourceWatcher
        ->extract( "Api", new ApiInput( $xmlUrl, 15 ), [
            "response_type" => "xml"
        ] )
        ->transform( "RenameColumns", [ "columns" => [ "description" => "description_text" ] ] )
        ->load( "Database", new DatabaseOutput( $connector ) )
        ->run();
    echo "Loaded menu from XML API into " . $sqlitePath . PHP_EOL;
} catch ( SourceWatcherException $e ) {
    fwrite( STDERR, "Error: " . $e->getMessage() . PHP_EOL );
    exit( 1 );
}
