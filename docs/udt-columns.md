# User-Defined Type Columns

The generator will recognize user-defined type from the schema, and then generate migration as

```php
public function up()
{
    Schema::create('table', function (Blueprint $table) {
        ...
    });
    DB::statement("ALTER TABLE table ADD column custom_type NOT NULL");
}
```

Note that the new `column` is always added at the end of the created `table` which means the ordering of the column generated in migration will differ from what we have from the schema.

Supported database with user-defined types:

- [x] PostgreSQL
- [x] SQL Server
