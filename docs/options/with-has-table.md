# With Has Table

```bash
--with-has-table
```

Checks for the existence of a table using the `Schema::hasTable` method before creating it.

### Example

```bash
php artisan migrate:generate --with-has-table
```

Will generate:

```php
if (!Schema::hasTable('users')) {
    Schema::create('users', function (Blueprint $table) {
        $table->bigIncrements('id');
        ...
    });
}
```
