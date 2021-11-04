<?php

/** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection PhpUnused */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ExpectedCreateUsers_DB_View extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE VIEW users_[db]_view AS (SELECT * from users_[db])");
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
