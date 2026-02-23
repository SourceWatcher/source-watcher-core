<?php

/**
 * Sample: Plain text file → SQLite.
 * Each line becomes one row. Uses samples/data/sample.txt and creates
 * samples/data/sqlite/lines-out.sqlite with table "lines" (column: line).
 */
require_once __DIR__ . "/bootstrap.php";

use Coco\SourceWatcher\Core\Database\Connections\SqliteConnector;
use Coco\SourceWatcher\Core\IO\Inputs\FileInput;
use Coco\SourceWatcher\Core\IO\Outputs\DatabaseOutput;
use Coco\SourceWatcher\Core\SourceWatcher;
use Coco\SourceWatcher\Core\SourceWatcherException;

$sqlitePath = __DIR__ . "/data/sqlite/lines-out.sqlite";
$dataDir   = dirname( $sqlitePath );

if ( !is_dir( $dataDir ) ) {
    mkdir( $dataDir, 0755, true );
}

$pdo = new PDO( "sqlite:" . $sqlitePath );
$pdo->exec( "CREATE TABLE IF NOT EXISTS lines (line TEXT)" );

$connector = new SqliteConnector();
$connector->setPath( $sqlitePath );
$connector->setTableName( "lines" );

$sourceWatcher = new SourceWatcher();

try {
    $sourceWatcher
        ->extract( "Txt", new FileInput( __DIR__ . "/data/sample.txt" ), [ "column" => "line" ] )
        ->load( "Database", new DatabaseOutput( $connector ) )
        ->run();
} catch ( SourceWatcherException $exception ) {
    echo sprintf( "Something unexpected went wrong: %s", $exception->getMessage() );
}
