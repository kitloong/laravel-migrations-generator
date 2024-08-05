<?php

use Illuminate\Support\Facades\DB;
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
        DB::statement(
            "CREATE VIEW " . $this->quoteIdentifier(DB::getTablePrefix() . 'quoted-name-view')
            . " AS SELECT * from " . $this->quoteIdentifier(DB::getTablePrefix() . 'quoted-name')
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS " . $this->quoteIdentifier('quoted-name-view'));
    }
};
