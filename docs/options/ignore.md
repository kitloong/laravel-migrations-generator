# Ignore

```bash
-i, --ignore[=IGNORE]
```

The `--ignore` option allows you to specify which tables you want to exclude when generating migrations. This is useful when you want to generate migrations for all tables in your database except for a few specific ones.

### Example

To use the `--ignore` option, you need to provide a comma-separated list of table names. For example:

```bash
php artisan migrate:generate --ignore="users,comments"
```
