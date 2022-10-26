<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

/** @noinspection PhpUnused */

use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Enum\Driver;
use KitLoong\MigrationsGenerator\Tests\TestMigration;

class ExpectedCreateQuotedName_DB_Proc extends TestMigration
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
                DB::unprepared(
                    "CREATE PROCEDURE findNameWithHyphen[db]()
                    BEGIN
                        SELECT * from " . $this->quoteIdentifier('name-with-hyphen-[db]') . ";
                    END"
                );
                break;
            case Driver::PGSQL():
                DB::unprepared(
                    "CREATE PROCEDURE findNameWithHyphen[db]()
                    language plpgsql
                    as $$
                    BEGIN
                        SELECT * from " . $this->quoteIdentifier('name-with-hyphen-[db]') . ";
                    END;$$"
                );
                break;
            case Driver::SQLSRV():
                DB::unprepared(
                    "CREATE PROCEDURE findNameWithHyphen[db]
                    AS
                    SELECT * from " . $this->quoteIdentifier('name-with-hyphen-[db]') . ";"
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
        DB::unprepared("DROP PROCEDURE IF EXISTS findNameWithHyphen[db]");
    }
}
