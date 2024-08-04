<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
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
        Schema::create('big_increments', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Increments');
        });

        Schema::create('increments', function (Blueprint $table) {
            $table->increments('id')->comment('Increments');
        });

        Schema::create('medium_increments', function (Blueprint $table) {
            $table->mediumIncrements('id')->comment('Increments');
        });

        Schema::create('small_increments', function (Blueprint $table) {
            $table->smallIncrements('id')->comment('Increments');
        });

        Schema::create('tiny_increments', function (Blueprint $table) {
            $table->tinyIncrements('id')->comment('Increments');
        });

        Schema::create('signed_increments', function (Blueprint $table) {
            $table->integer('id', true)->comment('Increments');
        });

//        Schema::create('increments_not_primary', function (Blueprint $table) {
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
        Schema::dropIfExists('big_increments');
        Schema::dropIfExists('increments');
        Schema::dropIfExists('medium_increments');
        Schema::dropIfExists('small_increments');
        Schema::dropIfExists('tiny_increments');
        Schema::dropIfExists('signed_increments');
    }
};
