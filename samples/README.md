# Samples

Run from repo root. Install deps first, or add `composer install --no-interaction --ignore-platform-reqs && ` before `php`.

**ApiJsonToSqlite**
Fetch users from JSONPlaceholder API, rename columns, load into SQLite.
```bash
docker run --rm -v "$(pwd)":/app -w /app/source-watcher-core composer:2 sh -c "php samples/ApiJsonToSqlite.php"
```

**ApiXmlToSqlite**
Fetch XML from a public API, rename columns, load into SQLite (uses `response_type` => `xml`).
```bash
docker run --rm -v "$(pwd)":/app -w /app/source-watcher-core composer:2 sh -c "php samples/ApiXmlToSqlite.php"
```

**CsvRenameColumnsToSqlite**
Read CSV, rename columns, write to SQLite.
```bash
docker run --rm -v "$(pwd)":/app -w /app/source-watcher-core composer:2 sh -c "php samples/CsvRenameColumnsToSqlite.php"
```

**CsvGuessGenderToSqlite**
Read CSV, rename columns, guess gender from first name, load into SQLite.
```bash
docker run --rm -v "$(pwd)":/app -w /app/source-watcher-core composer:2 sh -c "php samples/CsvGuessGenderToSqlite.php"
```

**JsonToSqlite**
Extract from a JSON file and load into SQLite.
```bash
docker run --rm -v "$(pwd)":/app -w /app/source-watcher-core composer:2 sh -c "php samples/JsonToSqlite.php"
```

**DatabaseConvertCaseToSqlite**
Extract from SQLite, convert column names to uppercase, load into another SQLite file.
```bash
docker run --rm -v "$(pwd)":/app -w /app/source-watcher-core composer:2 sh -c "php samples/DatabaseConvertCaseToSqlite.php"
```

**TxtToSqlite**
Read a text file (one line per row) and load into SQLite.
```bash
docker run --rm -v "$(pwd)":/app -w /app/source-watcher-core composer:2 sh -c "php samples/TxtToSqlite.php"
```

**CsvRenameColumnsToMysql**
Read CSV, rename columns, load into MySQL. Requires MySQL and `.env` with `UNIT_TEST_MYSQL_*`.
```bash
docker run --rm -v "$(pwd)":/app -w /app/source-watcher-core composer:2 sh -c "php samples/CsvRenameColumnsToMysql.php"
```
