<?php

namespace KitLoong\MigrationsGenerator\Tests;

use Illuminate\Database\Migrations\Migration;
use KitLoong\MigrationsGenerator\Support\AssetNameQuote;

abstract class TestMigration extends Migration
{
    use AssetNameQuote;
}
