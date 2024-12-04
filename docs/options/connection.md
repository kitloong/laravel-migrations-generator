# Connection

```bash
-c, --connection[=CONNECTION]
```

The `--connection` option allows you to specify the database connection to use. If you don't specify a connection, the default connection as defined in your Laravel's `config/database.php` will be used.

This option is particularly useful when you have multiple database connections defined in your Laravel application and you want to generate migrations for a specific connection.

### Example

Suppose you have a connection named `secondary` defined in your `config/database.php`. You can generate migrations for this connection by running:

```bash
php artisan migrate:generate --connection="secondary"
```

This command will generate migrations for the tables in the `secondary` database connection.

```php
// Up
Schema::connection('secondary')->create('users', function (Blueprint $table) {
    $table->bigIncrements('id');
    ...
});

// Down
Schema::connection('secondary')->dropIfExists('users');
```

If you are not sure what is the name of the connection you want to use, you can check the `connections` array in your `config/database.php` file.

```php
array_keys(config('database.connections'))
```
