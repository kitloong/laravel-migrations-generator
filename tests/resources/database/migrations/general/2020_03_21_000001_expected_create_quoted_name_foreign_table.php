<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
        Schema::create('quoted-name-foreign', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('quoted-name-id');

            // SQLite does not support alter add foreign key.
            // https://www.sqlite.org/omitted.html
            if (DB::getDriverName() !== Driver::SQLITE->value) {
                $table->foreign('quoted-name-id')->references('id')->on('quoted-name');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quoted-name-foreign');
    }
};
