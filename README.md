# Source Watcher Core

[![codecov](https://codecov.io/gh/TheCocoTeam/source-watcher-core/branch/master/graph/badge.svg)](https://codecov.io/gh/TheCocoTeam/source-watcher-core)

[![scrutinizer-ci](https://scrutinizer-ci.com/g/TheCocoTeam/source-watcher-core/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/TheCocoTeam/source-watcher-core/?branch=master)

**Source Watcher Core** is the ETL engine of the [Source Watcher](https://github.com/SourceWatcher/source-watcher-core) project. It can be used as a standalone library or as a dependency of [Source Watcher API](https://github.com/SourceWatcher/source-watcher-api) for server-side pipelines.

This is a PHP project that allows extracting, transforming, and loading data from and to different sources including databases, files, and services, while at the same time facilitating the transformation of the data in multiple ways.

## Requirements

- PHP 8.4 or later (see `composer.json` for extension requirements: curl, dom, json, pgsql, etc.)
- [Composer](https://getcomposer.org/)

## Optional system dependencies

Some extractors require external tools to be installed on the system (or in the Docker container). These are not PHP packages and are not managed by Composer.

| Extractor | Tool | Purpose | Install |
|---|---|---|---|
| `TesseractOcrExtractor` | [Tesseract OCR](https://github.com/tesseract-ocr/tesseract) | Extract text from images (PNG, JPEG, TIFF, etc.) | `apt-get install tesseract-ocr tesseract-ocr-eng` (Debian/Ubuntu) or `apk add tesseract-ocr tesseract-ocr-data-eng` (Alpine) |
| `PdfExtractor` | [poppler-utils](https://poppler.freedesktop.org/) + [Tesseract OCR](https://github.com/tesseract-ocr/tesseract) | Extract text from any PDF (digital, scanned, or mixed) | `apt-get install poppler-utils tesseract-ocr tesseract-ocr-eng` (Debian/Ubuntu) or `apk add poppler-utils tesseract-ocr tesseract-ocr-data-eng` (Alpine) |

Tesseract is an open-source OCR engine originally developed at HP and maintained by Google until 2017, now an independent project. It supports over 100 languages and is licensed under the [Apache License 2.0](https://github.com/tesseract-ocr/tesseract/blob/main/LICENSE). Language data packages (e.g. `tesseract-ocr-data-fra` for French) can be installed independently - see the [Tesseract documentation](https://github.com/tesseract-ocr/tesseract) for the full list.

`PdfExtractor` uses `pdftotext` for pages with an embedded text layer and automatically falls back to `pdftoppm` + Tesseract for image-only (scanned) pages. Mixed PDFs are handled page by page — no user configuration required.

The API's `Dockerfile` and `Dockerfile.dev` already include the installation steps for `tesseract-ocr`, `poppler-utils`, and the English language data.

## Installation

```bash
composer install
```

If the lock file is not installable on your PHP version, run **`composer update`** once. Without local PHP, from the repo root:  
`docker run --rm -v "$(pwd)":/app -w /app/source-watcher-core composer:2 sh -c "composer update --no-interaction --ignore-platform-reqs"`.

## Running tests

```bash
./vendor/bin/phpunit
```

## What is ETL?

ETL is an abbreviation that stands: for extract, transform, and load. It's a software process used to fill data warehouses with information in three steps:

- Extract: The process extracts or pulls data from multiple sources.

- Transform: The incoming data passes through a transformation step.

- Load: The ETL process will send the data to its final destination.

The foundations of ETL come from data warehousing methodologies dating back to the 1960s. ETL is the process of gathering raw data, like the one from production systems. Once collected, the data is transformed into a more readable, understandable format. The transformed and cleaned data is then loaded into a data repository, usually a relational database but not limited to other types of databases, files, and even REST services among others.

## ETL example

Assume that you have some information in a CSV file, and you want to import the content of the file to a MySQL database.

The CSV file is a standard comma-separated value file with some headers: *id*, *name* and *email*.

You have a table in your MySQL database with some fields: *id*, *name* and *email_address*.

First you would need to extract the information from your CSV file, only the fields that you want to insert into your MySQL database.

After you have extracted the information, you want to rename the CSV header email to match the database field email_address.

Finally, you want to save the information in your database table.

```php
<?php
use Coco\SourceWatcher\Core\Database\Connections\MySqlConnector;
use Coco\SourceWatcher\Core\IO\Inputs\FileInput;
use Coco\SourceWatcher\Core\IO\Outputs\DatabaseOutput;
use Coco\SourceWatcher\Core\Pipeline\SourceWatcher;
use Coco\SourceWatcher\Core\Exception\SourceWatcherException;

$mysqlConnector = new MySqlConnector();
$mysqlConnector->setUser( "user" );
$mysqlConnector->setPassword( "password" );
$mysqlConnector->setHost( "host" );
$mysqlConnector->setPort( 3306 );
$mysqlConnector->setDbName( "tests" );
$mysqlConnector->setTableName( "people" );

$sourceWatcher = new SourceWatcher();

try {
    $sourceWatcher
        ->extract( "Csv", new FileInput( __DIR__ . "/../data/csv/csv1.csv" ), [ "columns" => [ "name", "email" ] ] )
        ->transform( "RenameColumns", [ "columns" => [ "email" => "email_address" ] ] )
        ->load( "Database", new DatabaseOutput( $mysqlConnector ) )
        ->run();
} catch ( SourceWatcherException $exception ) {
    echo sprintf( "Something unexpected went wrong: %s", $exception->getMessage() );
}
```

A runnable version (requires `.env` with `UNIT_TEST_MYSQL_*` and a MySQL `people` table) is [samples/CsvRenameColumnsToMysql.php](samples/CsvRenameColumnsToMysql.php). More samples are listed in [samples/README.md](samples/README.md). Without local PHP, run the sample via the dev image as shown in the [quickstart README](https://github.com/SourceWatcher/source-watcher-quickstart).

## Feedback

Please submit issues, and send your feedback and suggestions as often as you have them.
