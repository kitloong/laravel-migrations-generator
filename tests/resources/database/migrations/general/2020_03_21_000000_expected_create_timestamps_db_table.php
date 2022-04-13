<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

/** @noinspection PhpUnused */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExpectedCreateTimestamps_DB_Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('timestamps_[db]', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
        });

        Schema::create('timestamps_precision_[db]', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps(2);
        });

        Schema::create('timestamps_tz_[db]', function (Blueprint $table) {
            $table->increments('id');
            $table->timestampsTz();
        });

        Schema::create('timestamps_tz_precision_[db]', function (Blueprint $table) {
            $table->increments('id');
            $table->timestampsTz(2);
        });

        Schema::create('not_timestamps_[db]', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamp('update_at')->nullable();
        });

        Schema::create('not_timestamps2_[db]', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('created_at');
            $table->timestamp('update_at')->nullable();
        });

        Schema::create('not_timestamps3_[db]', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('created_at')->nullable()->comment('Created at');
            $table->timestamp('update_at')->nullable();
        });

        Schema::create('not_timestamps4_[db]', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('update_at')->nullable()->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('not_timestamps_tz_[db]', function (Blueprint $table) {
            $table->increments('id');
            $table->timestampTz('created_at')->nullable();
            $table->timestampTz('deleted_at')->nullable();
            $table->timestampTz('update_at')->nullable();
        });

        Schema::create('not_timestamps_tz2_[db]', function (Blueprint $table) {
            $table->increments('id');
            $table->timestampTz('created_at');
            $table->timestampTz('update_at')->nullable();
        });

        Schema::create('not_timestamps_tz3_[db]', function (Blueprint $table) {
            $table->increments('id');
            $table->timestampTz('created_at')->nullable()->comment('Created at');
            $table->timestampTz('update_at')->nullable();
        });

        Schema::create('not_timestamps_tz4_[db]', function (Blueprint $table) {
            $table->increments('id');
            $table->timestampTz('created_at')->nullable();
            $table->timestampTz('update_at')->nullable()->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('timestamps_[db]');
        Schema::dropIfExists('timestamps_precision_[db]');
        Schema::dropIfExists('timestamps_tz_[db]');
        Schema::dropIfExists('timestamps_tz_precision_[db]');
        Schema::dropIfExists('not_timestamps_[db]');
        Schema::dropIfExists('not_timestamps2_[db]');
        Schema::dropIfExists('not_timestamps3_[db]');
        Schema::dropIfExists('not_timestamps4_[db]');
    }
}
