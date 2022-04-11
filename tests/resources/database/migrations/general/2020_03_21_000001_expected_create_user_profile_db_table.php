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
            $table->bigInteger('user_id_fk_custom')->unsigned();
            $table->bigInteger('user_id_fk_constraint')->unsigned();
            $table->unsignedBigInteger('user_sub_id');
            $table->unsignedBigInteger('user_sub_id_fk_custom');
            $table->unsignedBigInteger('user_sub_id_fk_constraint');
            $table->unsignedInteger('sub_id');
            $table->string('phone');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users_[db]');
            $table->foreign('user_id_fk_custom', 'users_[db]_foreign_custom')->references('id')->on('users_[db]');
            $table->foreign('user_id_fk_constraint', 'users_[db]_foreign_constraint')->references('id')->on(
                'users_[db]'
            )->onDelete('cascade')->onUpdate('cascade');
            $table->foreign(['user_id', 'user_sub_id'])->references(['id', 'sub_id'])->on('users_[db]');
            $table->foreign(['user_id', 'user_sub_id_fk_custom'], 'users_[db]_composite_foreign_custom')->references(
                ['id', 'sub_id']
            )->on('users_[db]');
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
