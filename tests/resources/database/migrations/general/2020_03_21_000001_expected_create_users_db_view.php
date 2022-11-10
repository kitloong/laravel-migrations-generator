<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

/** @noinspection PhpUnused */

use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Tests\TestMigration;

class ExpectedCreateUsers_DB_View extends TestMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE VIEW users_[db]_view AS SELECT * from " . DB::getTablePrefix() . "users_[db]");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS users_[db]_view");
    }
}
