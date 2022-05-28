<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

/** @noinspection PhpUnused */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExpectedCreateReservedNameModifier_DB_Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reserved_name_modifier_[db]', function (Blueprint $table) {
            $table->increments('id');
            $table->softDeletes()->nullable(false)->comment('Soft deletes')->default('2020-10-08');
            $table->rememberToken()->nullable(false)->comment('Remember token')->default('default');
        });

        Schema::create('reserved_name_modifier2_[db]', function (Blueprint $table) {
            $table->increments('id');
            $table->softDeletesTz()->nullable(false)->comment('Soft deletes tz')->default('2020-10-08');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reserved_name_modifier_[db]');
        Schema::dropIfExists('reserved_name_modifier2_[db]');
    }
}
