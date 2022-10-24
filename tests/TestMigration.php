<?php

namespace KitLoong\MigrationsGenerator\Tests;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

abstract class TestMigration extends Migration
{
    protected function quoteIdentifier(string $string): string
    {
        return DB::getDoctrineConnection()->quoteIdentifier($string);
    }
}
