<?php

/**
 * Sample: CSV → RenameColumns → GuessGender → SQLite.
 * Reads csv1 (name, email), renames to first_name/email_address, guesses gender from first name, loads to SQLite.
 * Uses samples/data/sqlite/people-gender-out.sqlite; creates table people_with_gender if needed.
 */
require_once __DIR__ . "/bootstrap.php";

use Coco\SourceWatcher\Core\Database\Connections\SqliteConnector;
use Coco\SourceWatcher\Core\IO\Inputs\FileInput;
use Coco\SourceWatcher\Core\IO\Outputs\DatabaseOutput;
use Coco\SourceWatcher\Core\Exception\SourceWatcherException;
use Coco\SourceWatcher\Core\Pipeline\SourceWatcher;

$sqlitePath = __DIR__ . "/data/sqlite/people-gender-out.sqlite";
$dataDir   = dirname( $sqlitePath );

if ( !is_dir( $dataDir ) ) {
    mkdir( $dataDir, 0755, true );
}

$pdo = new PDO( "sqlite:" . $sqlitePath );
$pdo->exec( "CREATE TABLE IF NOT EXISTS people_with_gender (first_name TEXT, email_address TEXT, gender TEXT)" );

$connector = new SqliteConnector();
$connector->setPath( $sqlitePath );
$connector->setTableName( "people_with_gender" );

$sourceWatcher = new SourceWatcher();

try {
    $sourceWatcher
        ->extract( "Csv", new FileInput( __DIR__ . "/data/csv/csv1.csv" ), [ "columns" => [ "name", "email" ] ] )
        ->transform( "RenameColumns", [ "columns" => [ "name" => "first_name", "email" => "email_address" ] ] )
        ->transform( "GuessGender", [ "firstNameField" => "first_name", "genderField" => "gender" ] )
        ->load( "Database", new DatabaseOutput( $connector ) )
        ->run();
} catch ( SourceWatcherException $exception ) {
    echo sprintf( "Something unexpected went wrong: %s", $exception->getMessage() );
}
