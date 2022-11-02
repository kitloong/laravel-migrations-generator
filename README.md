# Laravel Migrations Generator

![Style check CI](https://github.com/kitloong/laravel-migrations-generator/actions/workflows/check.yml/badge.svg?branch=6.x)
![Tests CI](https://github.com/kitloong/laravel-migrations-generator/actions/workflows/tests.yml/badge.svg?branch=6.x)
[![codecov](https://codecov.io/gh/kitloong/laravel-migrations-generator/branch/6.x/graph/badge.svg?token=U6ZRDPY6QZ)](https://codecov.io/gh/kitloong/laravel-migrations-generator)
[![Latest Stable Version](https://poser.pugx.org/kitloong/laravel-migrations-generator/v/stable.png)](https://packagist.org/packages/kitloong/laravel-migrations-generator)
[![Total Downloads](http://poser.pugx.org/kitloong/laravel-migrations-generator/downloads)](https://packagist.org/packages/kitloong/laravel-migrations-generator)
[![License](https://poser.pugx.org/kitloong/laravel-migrations-generator/license.png)](https://packagist.org/packages/kitloong/laravel-migrations-generator)

Generate Laravel Migrations from an existing database, including indexes and foreign keys!

This package is cloned from https://github.com/Xethron/migrations-generator and updated to support Laravel 5.6 and above, with more features.

## Supported Database

Laravel Migrations Generator supports all five Laravel first-party support databases:

- [x] MariaDB
- [x] MySQL
- [x] PostgreSQL
- [x] SQL Server
- [x] SQLite

## Version Compatibility

| Laravel       | Version                                         |
|---------------|-------------------------------------------------|
| 9.x           | 6.x                                             |
| 8.x           | 6.x                                             |
| 7.x           | 6.x                                             |
| 6.x           | 6.x                                             |
| 5.8.x         | 6.x                                             |
| 5.7.x         | 6.x                                             |
| 5.6.x         | 6.x                                             |
| 5.5 and below | https://github.com/Xethron/migrations-generator |

## Install

The recommended way to install this is through composer:

```bash
composer require --dev "kitloong/laravel-migrations-generator"
```

### Laravel Setup

Laravel will automatically register service provider for you.

### Lumen Setup

<details>
  <summary>Expand</summary>

Auto-discovery is not available in Lumen, you need some modification on `bootstrap/app.php`.

#### Enable Facade

Uncomment the following line.

```php
$app->withFacades();
```

#### Register Provider

Add following line into the `Register Service Providers` section.

```php
$app->register(\KitLoong\MigrationsGenerator\MigrationsGeneratorServiceProvider::class);
```
</details>

## Usage

To generate migrations from a database, you need to have your database setup in Laravel's config (`config/database.php`).

To create migrations for all the tables, run:

```bash
php artisan migrate:generate
```

You can specify the tables you wish to generate using:

```bash
php artisan migrate:generate --tables="table1,table2,table3,table4,table5"
```

You can also ignore tables with:

```bash
php artisan migrate:generate --ignore="table3,table4,table5"
```

Laravel Migrations Generator will first generate all the tables, columns and indexes, and afterwards setup all the foreign key constraints.

So make sure you include all the tables listed in the foreign keys so that they are present when the foreign keys are created.

You can also specify the connection name if you are not using your default connection with:

```bash
php artisan migrate:generate --connection="connection_name"
```

### Squash Migrations

By default, Generator will generate multiple migration files for each table. 

You can squash all migrations into a single file with:

```bash
php artisan migrate:generate --squash
```

### Options

Run `php artisan help migrate:generate` for a list of options.

| Options                              | Description                                                                                                                                                   |
|--------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------|
| -c, --connection[=CONNECTION]        | The database connection to use                                                                                                                                |
| -t, --tables[=TABLES]                | A list of Tables or Views you wish to Generate Migrations for separated by a comma: users,posts,comments                                                      |
| -i, --ignore[=IGNORE]                | A list of Tables or Views you wish to ignore, separated by a comma: users,posts,comments                                                                      |
| -p, --path[=PATH]                    | Where should the file be created?                                                                                                                             |
| -tp, --template-path[=TEMPLATE-PATH] | The location of the template for this generator                                                                                                               |
| --date[=DATE]                        | Migrations will be created with specified date. Views and Foreign keys will be created with + 1 second. Date should be in format supported by `Carbon::parse` |
| --table-filename[=TABLE-FILENAME]    | Define table migration filename, default pattern: `[datetime]\_create_[name]_table.php`                                                                       |
| --view-filename[=VIEW-FILENAME]      | Define view migration filename, default pattern: `[datetime]\_create_[name]_view.php`                                                                         |
| --proc-filename[=PROC-FILENAME]      | Define stored procedure filename, default pattern: `[datetime]\_create_[name]_proc.php`                                                                       |
| --fk-filename[=FK-FILENAME]          | Define foreign key migration filename, default pattern: `[datetime]\_add_foreign_keys_to_[name]_table.php`                                                    |
| --default-index-names                | Don\'t use DB index names for migrations                                                                                                                      |
| --default-fk-names                   | Don\'t use DB foreign key names for migrations                                                                                                                |
| --use-db-collation                   | Generate migrations with existing DB collation                                                                                                                |
| --skip-views                         | Don\'t generate views                                                                                                                                         |
| --skip-proc                          | Don\'t generate stored procedures                                                                                                                             |
| --squash                             | Generate all migrations into a single file                                                                                                                    |

## SQLite Alter Foreign Key

The generator first generates all tables and then adds foreign keys to existing tables.

However, SQLite only supports foreign keys upon creation of the table and not when tables are altered.
*_add_foreign_keys_* migrations will still be generated, however will get omitted if migrate to SQLite type database.

## User Defined Custom Column Type

The generator will register custom data type from the schema, then generate migration as

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

Supported DB:

- [x] PostgreSQL
- [x] SQL Server

## Thank You

Thanks to Bernhard Breytenbach for his great work. This package is cloned from https://github.com/Xethron/migrations-generator.

Thanks to Jeffrey Way for his amazing Laravel-4-Generators package. This package depends greatly on his work.

## Contributors

[![Contributors](https://contrib.rocks/image?repo=kitloong/laravel-migrations-generator)](https://github.com/kitloong/laravel-migrations-generator/graphs/contributors)

## License

The Laravel Migrations Generator is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
