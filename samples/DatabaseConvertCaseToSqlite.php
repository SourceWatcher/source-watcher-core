<?php

/**
 * Sample: Database (SQLite) extract → ConvertCase transformer → SQLite load.
 * Reads from people table in people-db.sqlite, converts column names to uppercase,
 * writes to people_upper in people-db-output.sqlite (separate file to avoid SQLite lock).
 */
require_once __DIR__ . "/bootstrap.php";

use Coco\SourceWatcher\Core\Database\Connections\SqliteConnector;
use Coco\SourceWatcher\Core\IO\Inputs\DatabaseInput;
use Coco\SourceWatcher\Core\IO\Outputs\DatabaseOutput;
use Coco\SourceWatcher\Core\SourceWatcher;
use Coco\SourceWatcher\Core\SourceWatcherException;
use Coco\SourceWatcher\Core\Transformers\ConvertCaseTransformer;

$sqlitePath = __DIR__ . "/data/sqlite/people-db.sqlite";
$outputPath = __DIR__ . "/data/sqlite/people-db-output.sqlite";
$dataDir   = dirname( $outputPath );

if ( !is_dir( $dataDir ) ) {
    mkdir( $dataDir, 0755, true );
}

$pdo = new PDO( "sqlite:" . $sqlitePath );
$pdo->exec( "CREATE TABLE IF NOT EXISTS people (name TEXT, email_address TEXT)" );

$stmt = $pdo->query( "SELECT COUNT(*) FROM people" );
if ( $stmt && (int) $stmt->fetchColumn() === 0 ) {
    $pdo->exec( "INSERT INTO people (name, email_address) VALUES ('Avery', 'avery@example.com'), ('Jane', 'jane@example.com'), ('John', 'john@example.com')" );
}

$pdoOut = new PDO( "sqlite:" . $outputPath );
$pdoOut->exec( "CREATE TABLE IF NOT EXISTS people_upper (NAME TEXT, EMAIL_ADDRESS TEXT)" );

$extractConnector = new SqliteConnector();
$extractConnector->setPath( $sqlitePath );

$loadConnector = new SqliteConnector();
$loadConnector->setPath( $outputPath );
$loadConnector->setTableName( "people_upper" );

$sourceWatcher = new SourceWatcher();

try {
    $sourceWatcher
        ->extract( "Database", new DatabaseInput( $extractConnector ), [ "query" => "SELECT name, email_address FROM people" ] )
        ->transform( "ConvertCase", [
            "columns" => [ "name", "email_address" ],
            "mode"    => ConvertCaseTransformer::CONVERT_CASE_MODE_UPPER
        ] )
        ->load( "Database", new DatabaseOutput( $loadConnector ) )
        ->run();
} catch ( SourceWatcherException $exception ) {
    echo sprintf( "Something unexpected went wrong: %s", $exception->getMessage() );
}
