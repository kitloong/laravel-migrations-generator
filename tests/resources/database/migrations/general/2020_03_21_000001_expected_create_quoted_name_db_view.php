<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

/** @noinspection PhpUnused */

use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Tests\TestMigration;

class ExpectedCreateQuotedName_DB_View extends TestMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement(
            "CREATE VIEW " . $this->quoteIdentifier(DB::getTablePrefix() . 'quoted-name-[db]-view')
            . " AS SELECT * from " . $this->quoteIdentifier(DB::getTablePrefix() . 'quoted-name-[db]')
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS " . $this->quoteIdentifier('quoted-name-[db]-view'));
    }
}
