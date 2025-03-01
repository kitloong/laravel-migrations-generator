# Default Index Names

```bash
--default-index-names
```

Use Laravel's default naming convention for indexes instead of the database index names.

### Example

```bash
php artisan migrate:generate --default-index-names
```

Schema with index:

```sql
CREATE TABLE `comments` (
    ...
    INDEX `user_id_idx` (`user_id`)
);
```

Will generate as:

```php
Schema::create('comments', function (Blueprint $table) {
    ...
    $table->index('user_id');
});
```
