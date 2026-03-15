# Log With Batch

```bash
--log-with-batch[=LOG-WITH-BATCH]
```

By default, the command will ask for the batch number to log the generated migrations.

```bash
Do you want to log these migrations in the migrations table? (yes/no) [yes]:
 > yes

 Next Batch Number is: 11. We recommend using Batch Number 0 so that it becomes the "first" migration. [Default: 0] [0]:
```

The `--log-with-batch` option allows you to specify the batch number for logging the generated migrations. 

By using `--log-with-batch`, you can skip the prompt for the batch number and directly log the migrations with the specified batch number.

### Example

```bash
php artisan migrate:generate --log-with-batch=0
```

Will generate the migration files and log them in the migrations table with `0` as the batch number.

```sql
SELECT migration FROM `migrations` WHERE batch = 0;

+----------------------------------------------------------+
| migration                                                |
+----------------------------------------------------------+
| 2024_10_08_083231_create_users_table.php                 |
| 2024_10_08_083231_create_comments_table.php              |
| 2024_10_08_083232_add_foreign_keys_to_comments_table.php |
+----------------------------------------------------------+
```
