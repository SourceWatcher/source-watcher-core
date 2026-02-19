# Source Watcher Core

[![codecov](https://codecov.io/gh/TheCocoTeam/source-watcher-core/branch/master/graph/badge.svg)](https://codecov.io/gh/TheCocoTeam/source-watcher-core)

[![scrutinizer-ci](https://scrutinizer-ci.com/g/TheCocoTeam/source-watcher-core/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/TheCocoTeam/source-watcher-core/?branch=master)

[![All Contributors](https://img.shields.io/badge/all_contributors-1-blue.svg?style=flat-square)](#contributors)

**Source Watcher Core** is the ETL engine of the [Source Watcher](https://github.com/TheCocoTeam/source-watcher) project. It can be used as a standalone library or as a dependency of [Source Watcher API](../source-watcher-api) for server-side pipelines.

This is a PHP project that allows extracting, transforming, and loading data from and to different sources including databases, files, and services, while at the same time facilitating the transformation of the data in multiple ways.

## Requirements

- PHP 7.4 or later (see `composer.json` for extension requirements: curl, dom, json, pgsql, etc.)
- [Composer](https://getcomposer.org/)

## Installation

```bash
composer install
```

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
use Coco\SourceWatcher\Core\SourceWatcher;
use Coco\SourceWatcher\Core\SourceWatcherException;

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
        ->extract( "Csv", new FileInput( __DIR__ . "/../data/csv/csv1.csv" ), [ "columns" => array( "name", "email" ) ] )
        ->transform( "RenameColumns", [ "columns" => array( "email" => "email_address" ) ] )
        ->load( "Database", new DatabaseOutput( $mysqlConnector ) )
        ->run();
} catch ( SourceWatcherException $exception ) {
    echo sprintf( "Something unexpected went wrong: %s", $exception->getMessage() );
}
```

A functional version of the code above can be found [here](samples/Core/SourceWatcher1.php)

## Feedback

Please submit issues, and send your feedback and suggestions as often as you have them.

## Contributors

<!-- ALL-CONTRIBUTORS-LIST:START - Do not remove or modify this section -->
<!-- prettier-ignore-start -->
<!-- markdownlint-disable -->
<table>
  <tr>
    <td align="center"><a href="http://bit.ly/jprv-linkedin"><img src="https://avatars0.githubusercontent.com/u/4614970?v=4" width="100px;" alt=""/><br /><sub><b>Jean Paul Ruiz</b></sub></a><br /><a href="https://github.com/TheCocoTeam/source-watcher-core/commits?author=jpruiz114" title="Code">💻</a></td>
  </tr>
</table>
<!-- markdownlint-enable -->
<!-- prettier-ignore-end -->
<!-- ALL-CONTRIBUTORS-LIST:END -->

This project follows the [all-contributors](https://github.com/all-contributors/all-contributors) specification. Contributions of any kind welcome!
