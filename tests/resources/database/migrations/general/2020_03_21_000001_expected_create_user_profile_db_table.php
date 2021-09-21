<?php

/** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection PhpUnused */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExpectedCreateUserProfile_DB_Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_profile_[db]', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('column-hyphen')->unsigned();
            $table->bigInteger('custom_name')->unsigned();
            $table->bigInteger('constraint')->unsigned();
            $table->unsignedInteger('sub_id');
            $table->timestampsTz();

            $table->foreign('user_id')->references('id')->on('users_[db]');
            $table->foreign('custom_name', 'custom_foreign')->references('id')->on('users_[db]');
            $table->foreign('column-hyphen')->references('id')->on('users_[db]');
            $table->foreign(['user_id', 'sub_id'])->references(['id', 'sub_id'])->on('users_[db]');
            $table->foreign('constraint')->references('id')->on('users_[db]')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_profile_[db]');
    }
}
