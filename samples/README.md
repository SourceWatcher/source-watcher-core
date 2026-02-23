# Samples

Run from repo root. Install deps first, or add `composer install --no-interaction --ignore-platform-reqs && ` before `php`.

**CsvRenameColumnsToSqlite**
```bash
docker run --rm -v "$(pwd)":/app -w /app/source-watcher-core composer:2 sh -c "php samples/CsvRenameColumnsToSqlite.php"
```

**CsvGuessGenderToSqlite**
```bash
docker run --rm -v "$(pwd)":/app -w /app/source-watcher-core composer:2 sh -c "php samples/CsvGuessGenderToSqlite.php"
```

**JsonToSqlite**
```bash
docker run --rm -v "$(pwd)":/app -w /app/source-watcher-core composer:2 sh -c "php samples/JsonToSqlite.php"
```

**DatabaseConvertCaseToSqlite**
```bash
docker run --rm -v "$(pwd)":/app -w /app/source-watcher-core composer:2 sh -c "php samples/DatabaseConvertCaseToSqlite.php"
```

**TxtToSqlite**
```bash
docker run --rm -v "$(pwd)":/app -w /app/source-watcher-core composer:2 sh -c "php samples/TxtToSqlite.php"
```

**CsvRenameColumnsToMysql** (requires MySQL and `.env` with `UNIT_TEST_MYSQL_*`)
```bash
docker run --rm -v "$(pwd)":/app -w /app/source-watcher-core composer:2 sh -c "php samples/CsvRenameColumnsToMysql.php"
```
