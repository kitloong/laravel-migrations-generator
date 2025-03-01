# Template Path

```bash
-tp, --template-path[=TEMPLATE-PATH]
```

The `--template-path` option allows you to specify the location of the template for this generator. This is useful when you want to use your own template for generating migrations.

Customize your template using the [default stub](https://github.com/kitloong/laravel-migrations-generator/blob/7.x/stubs/migration.generate.stub), and make sure to include the following placeholders:

1. `{{ use }}` 
2. `{{ up }}`
3. `{{ down }}`

The placeholder should be self-explanatory in [default stub](https://github.com/kitloong/laravel-migrations-generator/blob/7.x/stubs/migration.generate.stub).

### Example

To use the `--template-path` option, you need to provide the path to the directory containing the template files. For example:

```bash
php artisan migrate:generate --template-path="custom/templates"
```

This command will use the templates located in the `custom/templates` directory for generating the migration files.
