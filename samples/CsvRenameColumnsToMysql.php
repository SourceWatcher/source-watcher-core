<?php

/**
 * Sample: CSV file → RenameColumns transformer → MySQL.
 * Requires .env with UNIT_TEST_MYSQL_* and a "people" table (name, email_address).
 */
require_once __DIR__ . "/bootstrap.php";

use Coco\SourceWatcher\Core\Database\Connections\MySqlConnector;
use Coco\SourceWatcher\Core\IO\Inputs\FileInput;
use Coco\SourceWatcher\Core\IO\Outputs\DatabaseOutput;
use Coco\SourceWatcher\Core\SourceWatcher;
use Coco\SourceWatcher\Core\SourceWatcherException;
use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;

$envPath = __DIR__ . "/../";

try {
    $dotenv = Dotenv::createImmutable( $envPath );
    $dotenv->load();
} catch ( InvalidPathException $e ) {
    fwrite( STDERR, "Error: No .env file found at " . realpath( $envPath ) . " (or path invalid)." . PHP_EOL );
    fwrite( STDERR, "Create a .env file in source-watcher-core/ with: UNIT_TEST_MYSQL_USERNAME, UNIT_TEST_MYSQL_PASSWORD, UNIT_TEST_MYSQL_HOST, UNIT_TEST_MYSQL_PORT, UNIT_TEST_MYSQL_DATABASE" . PHP_EOL );
    exit( 1 );
}

$required = [ "UNIT_TEST_MYSQL_USERNAME", "UNIT_TEST_MYSQL_PASSWORD", "UNIT_TEST_MYSQL_HOST", "UNIT_TEST_MYSQL_PORT", "UNIT_TEST_MYSQL_DATABASE" ];
$missing = array_filter( $required, fn( string $key ) => empty( $_ENV[$key] ) );
if ( !empty( $missing ) ) {
    fwrite( STDERR, "Error: Missing required env variable(s): " . implode( ", ", $missing ) . PHP_EOL );
    fwrite( STDERR, "Set them in source-watcher-core/.env" . PHP_EOL );
    exit( 1 );
}

$mysqlConnector = new MySqlConnector();
$mysqlConnector->setUser( $_ENV["UNIT_TEST_MYSQL_USERNAME"] );
$mysqlConnector->setPassword( $_ENV["UNIT_TEST_MYSQL_PASSWORD"] );
$mysqlConnector->setHost( $_ENV["UNIT_TEST_MYSQL_HOST"] );
$mysqlConnector->setPort( (int) $_ENV["UNIT_TEST_MYSQL_PORT"] );
$mysqlConnector->setDbName( $_ENV["UNIT_TEST_MYSQL_DATABASE"] );
$mysqlConnector->setTableName( "people" );

$sourceWatcher = new SourceWatcher();

try {
    $sourceWatcher
        ->extract( "Csv", new FileInput( __DIR__ . "/data/csv/csv1.csv" ), [ "columns" => [ "name", "email" ] ] )
        ->transform( "RenameColumns", [ "columns" => [ "email" => "email_address" ] ] )
        ->load( "Database", new DatabaseOutput( $mysqlConnector ) )
        ->run();
} catch ( SourceWatcherException $exception ) {
    fwrite( STDERR, "Error: " . $exception->getMessage() . PHP_EOL );
    exit( 1 );
}
