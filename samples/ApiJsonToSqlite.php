<?php

/**
 * Full ETL (JSON): API → Transform → SQLite.
 * Extract: GET https://jsonplaceholder.typicode.com/users via ApiExtractor with "responseType" => "json".
 * Transform: RenameColumns (email → email_address).
 * Load: SQLite table "users" (id, name, email_address).
 *
 * Safe public API; no auth. Run from repo root.
 */
require_once __DIR__ . "/bootstrap.php";

use Coco\SourceWatcher\Core\Database\Connections\SqliteConnector;
use Coco\SourceWatcher\Core\IO\Inputs\ApiInput;
use Coco\SourceWatcher\Core\IO\Outputs\DatabaseOutput;
use Coco\SourceWatcher\Core\Exception\SourceWatcherException;
use Coco\SourceWatcher\Core\Pipeline\SourceWatcher;

$sqlitePath = __DIR__ . "/data/sqlite/jsonplaceholder-users.sqlite";
$dataDir = dirname( $sqlitePath );
if ( !is_dir( $dataDir ) ) {
    mkdir( $dataDir, 0755, true );
}

$pdo = new PDO( "sqlite:" . $sqlitePath );
$pdo->exec( "CREATE TABLE IF NOT EXISTS users (id INTEGER, name TEXT, email_address TEXT)" );

$connector = new SqliteConnector();
$connector->setPath( $sqlitePath );
$connector->setTableName( "users" );

$sourceWatcher = new SourceWatcher();

try {
    $sourceWatcher
        ->extract( "Api", new ApiInput( "https://jsonplaceholder.typicode.com/users", 15 ), [
            "response_type" => "json",
            "columns"      => [
                "id"    => "$[*].id",
                "name"  => "$[*].name",
                "email" => "$[*].email"
            ]
        ] )
        ->transform( "RenameColumns", [ "columns" => [ "email" => "email_address" ] ] )
        ->load( "Database", new DatabaseOutput( $connector ) )
        ->run();
    echo "Loaded users from JSON API into " . $sqlitePath . PHP_EOL;
} catch ( SourceWatcherException $e ) {
    fwrite( STDERR, "Error: " . $e->getMessage() . PHP_EOL );
    exit( 1 );
}
