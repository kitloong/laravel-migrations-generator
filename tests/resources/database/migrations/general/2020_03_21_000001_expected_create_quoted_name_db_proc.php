<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

/** @noinspection PhpUnused */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Enum\Driver;

class ExpectedCreateQuotedName_DB_Proc extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        switch (DB::getDriverName()) {
            case Driver::MYSQL():
                DB::statement(
                    "CREATE PROCEDURE findNameWithHyphen()
                    BEGIN
                        SELECT * from ".$this->quoteIdentifier('name-with-hyphen-[db]').";
                    END"
                );
                break;
            case Driver::PGSQL():
                DB::statement(
                    "CREATE PROCEDURE findNameWithHyphen()
                    language plpgsql
                    as $$
                    BEGIN
                        SELECT * from ".$this->quoteIdentifier('name-with-hyphen-[db]').";
                    END;$$"
                );
                break;
            case Driver::SQLSRV():
                DB::statement(
                    "CREATE PROCEDURE findNameWithHyphen
                    AS
                    SELECT * from ".$this->quoteIdentifier('name-with-hyphen-[db]').";"
                );
                break;
            default:
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP PROCEDURE IF EXISTS findNameWithHyphen");
    }

    private function quoteIdentifier(string $string): string
    {
        return DB::getDoctrineConnection()->quoteIdentifier($string);
    }
}
