# Table Filename

```bash
--table-filename[=TABLE-FILENAME]
```

The `--table-filename` option allows you to define the filename pattern for table migrations. 

By default, the pattern is `[datetime]_create_[name]_table.php`.  

### Example

```bash
php artisan migrate:generate --table-filename="[datetime]_create_mysql_[name]_table.php"
```

Will generate the migration files with the specified pattern:

```bash
2024_10_08_083231_create_mysql_comments_table.php
2024_10_08_083231_create_mysql_users_table.php
```

`--table-filename`, `--view-filename`, `--proc-filename`, and `--fk-filename` share the similar usage and purpose.
