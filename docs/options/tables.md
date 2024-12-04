# Tables

```bash
-t, --tables[=TABLES]
```

By default, the `migrate:generate` command will generate migrations for all tables and views in your database.

The `--tables` option allows you to specify which tables you want to generate migrations for. This is useful when you only want to generate migrations for specific tables or views in your database.

### Example

To use the `--tables` option, you need to provide a comma-separated list of table / view names.

```bash
php artisan migrate:generate --tables="users,posts,comments"
```
