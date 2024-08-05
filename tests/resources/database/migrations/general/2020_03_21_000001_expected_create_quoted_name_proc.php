<?php

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
            case Driver::MARIADB->value:
            case Driver::MYSQL->value:
                DB::unprepared(
                    "CREATE PROCEDURE findNameWithHyphen()
                    BEGIN
                        SELECT * from " . $this->quoteIdentifier('name-with-hyphen') . ";
                    END"
                );
                break;
            case Driver::PGSQL->value:
                DB::unprepared(
                    "CREATE PROCEDURE findNameWithHyphen()
                    language plpgsql
                    as $$
                    BEGIN
                        SELECT * from " . $this->quoteIdentifier('name-with-hyphen') . ";
                    END;$$"
                );
                break;
            case Driver::SQLSRV->value:
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
