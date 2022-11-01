<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

/** @noinspection PhpUnused */

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use KitLoong\MigrationsGenerator\Tests\TestMigration;

class ExpectedCreateIncrements_DB_Table extends TestMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('big_increments_[db]', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Increments');
        });

        Schema::create('increments_[db]', function (Blueprint $table) {
            $table->increments('id')->comment('Increments');
        });

        Schema::create('medium_increments_[db]', function (Blueprint $table) {
            $table->mediumIncrements('id')->comment('Increments');
        });

        Schema::create('small_increments_[db]', function (Blueprint $table) {
            $table->smallIncrements('id')->comment('Increments');
        });

        Schema::create('tiny_increments_[db]', function (Blueprint $table) {
            $table->tinyIncrements('id')->comment('Increments');
        });

        Schema::create('signed_increments_[db]', function (Blueprint $table) {
            $table->integer('id', true)->comment('Increments');
        });

//        Schema::create('increments_not_primary_[db]', function (Blueprint $table) {
//            $table->increments('id');
//            $table->unique('id');
//            $table->dropPrimary();
//        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('big_increments_[db]');
        Schema::dropIfExists('increments_[db]');
        Schema::dropIfExists('medium_increments_[db]');
        Schema::dropIfExists('small_increments_[db]');
        Schema::dropIfExists('tiny_increments_[db]');
        Schema::dropIfExists('signed_increments_[db]');
    }
}
