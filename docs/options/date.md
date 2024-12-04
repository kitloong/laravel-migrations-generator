# Date

```bash
--date[=DATE]
```

The `--date` option allows you to specify the date for the generated migration files. 

Migrations will be created with the specified date. Views, procedures, and foreign keys will be created with an additional 1 second.

The date should be in a format supported by [`Carbon::parse`](https://github.com/briannesbitt/Carbon/blob/d481d8d69a94dc00e5c1b147ec1f7e7492626b12/src/Carbon/Traits/Creator.php#L207).

### Example

```bash
php artisan migrate:generate --date="2024-10-08 12:30:00"
```

Will generate the following migrations:

```bash
2024_10_08_123000_create_comments_table.php
2024_10_08_123000_create_users_table.php
2024_10_08_123001_add_foreign_keys_to_comments_table.php
```
