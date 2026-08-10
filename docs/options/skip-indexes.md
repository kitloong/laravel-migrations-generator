# Skip Indexes

```bash
--skip-indexes
```

Without a value, excludes every secondary index from generated table migrations while retaining primary keys:

```bash
php artisan migrate:generate --skip-indexes
```

To exclude only specific index types, provide a comma-separated list. The names are case-insensitive and match Laravel's index methods:

```bash
php artisan migrate:generate --skip-indexes=fulltext,primary,unique
```

Supported values are `index`, `primary`, `unique`, `spatialIndex`, and `fullText`.
