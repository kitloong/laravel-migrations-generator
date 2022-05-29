# Laravel Migrations Generator

![Style check CI](https://github.com/kitloong/laravel-migrations-generator/actions/workflows/check.yml/badge.svg?branch=6.x)
[![codecov](https://codecov.io/gh/kitloong/laravel-migrations-generator/branch/6.x/graph/badge.svg?token=U6ZRDPY6QZ)](https://codecov.io/gh/kitloong/laravel-migrations-generator)
![Tests CI](https://github.com/kitloong/laravel-migrations-generator/actions/workflows/tests.yml/badge.svg?branch=6.x)
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

|Laravel|Version|
|---|---|
|9.x|6.x|
|8.x|6.x|
|7.x|6.x|
|6.x|6.x|
|5.8.x|6.x|
|5.7.x|6.x|
|5.6.x|6.x|
|5.5 and below|https://github.com/Xethron/migrations-generator|

## Install

The recommended way to install this is through composer:

```bash
composer require --dev "kitloong/laravel-migrations-generator"
```

### Laravel Setup

Laravel will automatically register service provider for you.

### Lumen Setup

Auto-discovery is not available in Lumen, you need some modification on `bootstrap/app.php`.

#### Enable facade

Uncomment the following line.

```
$app->withFacades();
```

#### Register provider

Add following line into the `Register Service Providers` section.

```
$app->register(\KitLoong\MigrationsGenerator\MigrationsGeneratorServiceProvider::class);
```

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

### Squash migrations

By default, Generator will generate multiple migration files for each table. 

You can squash all migrations into a single file with:

```bash
php artisan migrate:generate --squash
```

### Options

Run `php artisan help migrate:generate` for a list of options.

|Options|Description|
|---|---|
|-c, --connection[=CONNECTION]|The database connection to use|
|-t, --tables[=TABLES]|A list of Tables or Views you wish to Generate Migrations for separated by a comma: users,posts,comments|
|-i, --ignore[=IGNORE]|A list of Tables or Views you wish to ignore, separated by a comma: users,posts,comments|
|-p, --path[=PATH]|Where should the file be created?|
|-tp, --template-path[=TEMPLATE-PATH]|The location of the template for this generator|
|--date[=DATE]|Migrations will be created with specified date. Views and Foreign keys will be created with + 1 second. Date should be in format suitable for `Carbon::parse`|
|--table-filename[=TABLE-FILENAME]|Define table migration filename, default pattern: `[datetime_prefix]\_create_[table]_table.php`|
|--view-filename[=VIEW-FILENAME]|Define view migration filename, default pattern: `[datetime_prefix]\_create_[table]_view.php`|
|--fk-filename[=FK-FILENAME]|Define foreign key migration filename, default pattern: `[datetime_prefix]\_add_foreign_keys_to_[table]_table.php`|
|--default-index-names|Don\'t use db index names for migrations|
|--default-fk-names|Don\'t use db foreign key names for migrations|
|--use-db-collation|Follow db collations for migrations|
|--skip-views|Don\'t generate views|
|--squash|Generate all migrations into a single file|

## SQLite alter foreign key

The generator first generates all tables and then adds foreign keys to existing tables.
However, SQLite only supports foreign keys upon creation of the table and not when tables are altered.
*_add_foreign_keys_* migrations will still be generated, however will get omitted if migrate to SQLite type database.

## Thank You

Thanks to Bernhard Breytenbach for his great work. This package is cloned from https://github.com/Xethron/migrations-generator.

Thanks to Jeffrey Way for his amazing Laravel-4-Generators package. This package depends greatly on his work.

## Contributors

[![Contributors](https://contrib.rocks/image?repo=kitloong/laravel-migrations-generator)](https://github.com/kitloong/laravel-migrations-generator/graphs/contributors)

## License

The Laravel Migrations Generator is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
