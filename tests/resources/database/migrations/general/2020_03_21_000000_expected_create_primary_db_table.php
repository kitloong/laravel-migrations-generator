<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

/** @noinspection PhpUnused */

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use KitLoong\MigrationsGenerator\Tests\TestMigration;

class ExpectedCreatePrimary_DB_Table extends TestMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('primary_id_[db]', function (Blueprint $table) {
            $table->unsignedInteger('id');
            $table->primary('id');
        });

        Schema::create('primary_name_[db]', function (Blueprint $table) {
            $table->string('name');
            $table->primary('name', 'primary_custom');
        });

        Schema::create('signed_primary_id_[db]', function (Blueprint $table) {
            $table->integer('id');
            $table->primary('id');
        });

        Schema::create('composite_primary_[db]', function (Blueprint $table) {
            $table->unsignedInteger('id');
            $table->unsignedInteger('sub_id');
            $table->primary(['id', 'sub_id']);
        });

        // Test short table name
        Schema::create('s_[db]', function (Blueprint $table) {
            $table->bigIncrements('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('primary_id_[db]');
        Schema::dropIfExists('primary_name_[db]');
        Schema::dropIfExists('signed_primary_id_[db]');
        Schema::dropIfExists('composite_primary_[db]');
        Schema::dropIfExists('s_[db]');
    }
}
