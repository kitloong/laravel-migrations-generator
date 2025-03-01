# Default Foreign Key Names

```bash
--default-fk-names
```

Use Laravel's default naming convention for foreign keys instead of the database foreign key names.

### Example

```bash
php artisan migrate:generate --default-fk-names
```

Schema with foreign key:

```sql
CREATE TABLE `comments` (
    ...
    CONSTRAINT `user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
);
```

Will generate as:

```php
Schema::table('comments', function (Blueprint $table) {
    ...
    $table->foreign('user_id')->references('id')->on('users');
});
```
