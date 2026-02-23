<?php

/**
 * Sample: CSV file → RenameColumns transformer → SQLite.
 * No MySQL or .env required. Uses samples/data/sqlite/people-db.sqlite (table: people).
 */
require_once __DIR__ . "/bootstrap.php";

use Coco\SourceWatcher\Core\Database\Connections\SqliteConnector;
use Coco\SourceWatcher\Core\IO\Inputs\FileInput;
use Coco\SourceWatcher\Core\IO\Outputs\DatabaseOutput;
use Coco\SourceWatcher\Core\SourceWatcher;
use Coco\SourceWatcher\Core\SourceWatcherException;

$sqlitePath = __DIR__ . "/data/sqlite/people-db.sqlite";

$connector = new SqliteConnector();
$connector->setPath( $sqlitePath );
$connector->setTableName( "people" );

$sourceWatcher = new SourceWatcher();

try {
    $sourceWatcher
        ->extract( "Csv", new FileInput( __DIR__ . "/data/csv/csv1.csv" ), [ "columns" => [ "name", "email" ] ] )
        ->transform( "RenameColumns", [ "columns" => [ "email" => "email_address" ] ] )
        ->load( "Database", new DatabaseOutput( $connector ) )
        ->run();
} catch ( SourceWatcherException $exception ) {
    echo sprintf( "Something unexpected went wrong: %s", $exception->getMessage() );
}
