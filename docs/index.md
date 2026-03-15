# Getting Started

Generate Laravel Migrations from an existing database, including indexes and foreign keys!

## Supported Database

Laravel Migrations Generator supports all five Laravel first-party support databases:

- MariaDB
- MySQL
- PostgreSQL
- SQL Server
- SQLite

## Install

The recommended way to install this is through composer:

```bash
composer require --dev kitloong/laravel-migrations-generator
```

## Version Compatibility

| Laravel            | Version                                         |
|--------------------|-------------------------------------------------|
| 12.x               | 7.x                                             |
| 11.x               | 7.x                                             |
| \>= 10.43.x        | 7.x                                             |
| 10.x \| <= 10.42.x | 6.x                                             |
| 9.x                | 6.x                                             |
| 8.x                | 6.x                                             |
| 7.x                | 6.x                                             |
| 6.x                | 6.x                                             |
| 5.8.x              | 6.x                                             |
| 5.7.x              | 6.x                                             |
| 5.6.x              | 6.x                                             |
| <= 5.5.x           | https://github.com/Xethron/migrations-generator |

### Laravel Setup

Laravel will automatically register service provider for you.

### Lumen Setup

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
