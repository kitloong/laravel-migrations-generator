<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

/** @noinspection PhpUnused */

use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Enum\Driver;
use KitLoong\MigrationsGenerator\Tests\TestMigration;

return new class extends TestMigration
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
                    "CREATE PROCEDURE findNameWithHyphen()
                    BEGIN
                        SELECT * from " . $this->quoteIdentifier('name-with-hyphen') . ";
                    END"
                );
                break;
            case Driver::PGSQL():
                DB::unprepared(
                    "CREATE PROCEDURE findNameWithHyphen()
                    language plpgsql
                    as $$
                    BEGIN
                        SELECT * from " . $this->quoteIdentifier('name-with-hyphen') . ";
                    END;$$"
                );
                break;
            case Driver::SQLSRV():
                DB::unprepared(
                    "CREATE PROCEDURE findNameWithHyphen
                    AS
                    SELECT * from " . $this->quoteIdentifier('name-with-hyphen') . ";"
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
        DB::unprepared("DROP PROCEDURE IF EXISTS findNameWithHyphen");
    }
};
