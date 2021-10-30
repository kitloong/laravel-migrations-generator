<?php

/** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection PhpUnused */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ExpectedCreateNameWithHyphen_DB_View extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE VIEW ".$this->quoteIdentifier('name-with-hyphen-[db]_view')." AS (SELECT * from ".$this->quoteIdentifier('name-with-hyphen-[db]').")");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS ".$this->quoteIdentifier('name-with-hyphen-[db]_view'));
    }

    private function quoteIdentifier(string $string): string
    {
        if (config('database.default') === 'pgsql' || config('database.default') === 'sqlsrv') {
            return '"'.$string.'"';
        } else {
            return '`'.$string.'`';
        }
    }
}
