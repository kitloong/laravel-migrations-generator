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
        Schema::create('timestamps', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
        });

        Schema::create('timestamps_precision', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps(2);
        });

        Schema::create('timestamps_tz', function (Blueprint $table) {
            $table->increments('id');
            $table->timestampsTz();
        });

        Schema::create('timestamps_tz_precision', function (Blueprint $table) {
            $table->increments('id');
            $table->timestampsTz(2);
        });

        Schema::create('not_timestamps', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamp('update_at')->nullable();
        });

        Schema::create('not_timestamps2', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('created_at');
            $table->timestamp('update_at')->nullable();
        });

        Schema::create('not_timestamps3', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('created_at')->nullable()->comment('Created at');
            $table->timestamp('update_at')->nullable();
        });

        Schema::create('not_timestamps4', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('update_at')->nullable()->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('not_timestamps_tz', function (Blueprint $table) {
            $table->increments('id');
            $table->timestampTz('created_at')->nullable();
            $table->timestampTz('deleted_at')->nullable();
            $table->timestampTz('update_at')->nullable();
        });

        Schema::create('not_timestamps_tz2', function (Blueprint $table) {
            $table->increments('id');
            $table->timestampTz('created_at');
            $table->timestampTz('update_at')->nullable();
        });

        Schema::create('not_timestamps_tz3', function (Blueprint $table) {
            $table->increments('id');
            $table->timestampTz('created_at')->nullable()->comment('Created at');
            $table->timestampTz('update_at')->nullable();
        });

        Schema::create('not_timestamps_tz4', function (Blueprint $table) {
            $table->increments('id');
            $table->timestampTz('created_at')->nullable();
            $table->timestampTz('update_at')->nullable()->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('use_current_on_update', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('datetime_useCurrentOnUpdate_nullable_useCurrent')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->dateTime('datetime_useCurrentOnUpdate_useCurrent')->useCurrentOnUpdate()->useCurrent();
            $table->dateTime('datetime_nullable')->useCurrentOnUpdate()->nullable();
            $table->dateTime('datetime_useCurrent')->useCurrent();
            $table->timestamp('timestamp_useCurrentOnUpdate_nullable_useCurrent')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->timestamp('timestamp_useCurrentOnUpdate_useCurrent')->useCurrentOnUpdate()->useCurrent();
            $table->timestamp('timestamp_nullable')->useCurrentOnUpdate()->nullable();
            $table->timestamp('timestamp_useCurrent')->useCurrent();
            $table->timestamp('timestamp_useCurrentOnUpdate')->useCurrentOnUpdate()->default('2024-10-08 00:00:00');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('timestamps');
        Schema::dropIfExists('timestamps_precision');
        Schema::dropIfExists('timestamps_tz');
        Schema::dropIfExists('timestamps_tz_precision');
        Schema::dropIfExists('not_timestamps');
        Schema::dropIfExists('not_timestamps2');
        Schema::dropIfExists('not_timestamps3');
        Schema::dropIfExists('not_timestamps4');
        Schema::dropIfExists('use_current_on_update');
    }
};
