# Path

```bash
-p, --path[=PATH]
```

The `--path` option allows you to specify the directory where the generated migration files should be created. 

By default, migrations are created in the `database/migrations` directory.  

## Example

To use the `--path` option, you need to provide the desired directory path. For example:

```bash
php artisan migrate:generate --path="custom/migrations"
```

This command will generate the migration files in the `custom/migrations` directory instead of the default `database/migrations` directory.
