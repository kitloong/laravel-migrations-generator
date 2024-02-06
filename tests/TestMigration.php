<?php

namespace KitLoong\MigrationsGenerator\Tests;

use Illuminate\Database\Migrations\Migration;
use KitLoong\MigrationsGenerator\DBAL\Connection;

abstract class TestMigration extends Migration
{
    protected function quoteIdentifier(string $string): string
    {
        return app(Connection::class)->getDoctrineConnection()->quoteIdentifier($string);
    }
}
