<?php

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
            $table->integer('id');
            $table->bigInteger('user_id')->unsigned();
            $table->unsignedInteger('sub_id');

            $table->primary('id');
//            $table->foreign('user_id')->references('id')->on('users_[db]');
            $table->foreign(['user_id', 'sub_id'])->references(['id', 'sub_id'])->on('users_[db]');
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
