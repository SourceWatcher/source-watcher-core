<?php

/**
 * Sample: ApiReader with a safe public API (JSONPlaceholder).
 * Fetches https://jsonplaceholder.typicode.com/users and prints a short summary.
 * No auth, read-only, and the endpoint is stable for demos.
 */
require_once __DIR__ . "/bootstrap.php";

use Coco\SourceWatcher\Core\Api\ApiReader;
use Coco\SourceWatcher\Core\SourceWatcherException;

$url = "https://jsonplaceholder.typicode.com/users";

$reader = new ApiReader();
$reader->setResourceURL( $url );
$reader->setTimeout( 10 );

try {
    $response = $reader->read();
} catch ( SourceWatcherException $e ) {
    fwrite( STDERR, "Error: " . $e->getMessage() . PHP_EOL );
    exit( 1 );
}

$data = json_decode( $response, true );
if ( !is_array( $data ) ) {
    fwrite( STDERR, "Error: Invalid JSON from API" . PHP_EOL );
    exit( 1 );
}

$count = count( $data );
$first = $data[0] ?? null;
$name = $first["name"] ?? "(none)";

echo sprintf( "Fetched %d users. First: %s" . PHP_EOL, $count, $name );
