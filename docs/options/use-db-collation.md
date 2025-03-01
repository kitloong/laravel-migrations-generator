# Use Database Collation

```bash
--use-db-collation
```

By default, migration files are generated without collation setting.

This means `php artisan migrate` will create tables with the collation settings defined in the `config/database.php` file.

```php
// config/database.php

return [
    'connections' => [
        'mysql' => [
            ...
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ],
    ],
];
```

The `--use-db-collation` option will always generates migrations with the existing database collation settings.

### Example

```bash
php artisan migrate:generate --use-db-collation
```

Will generate: 

```php
Schema::create('users', function (Blueprint $table) {
    $table->collation = 'utf8mb4_unicode_ci';
    $table->charset = 'utf8mb4';

    $table->bigIncrements('id');
    ...
});
```
