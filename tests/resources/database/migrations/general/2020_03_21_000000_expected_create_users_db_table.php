<?php

/** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection PhpUnused */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExpectedCreateUsers_DB_Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_[db]', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->unsignedInteger('sub_id');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('column-with-hyphen');
            $table->softDeletes()->comment('Soft delete');
            $table->softDeletes('deleted_at2', 2)->comment('Soft delete');
            $table->rememberToken()->comment('Remember token');
            $table->timestamps();

            $table->primary('id');
            $table->unique(['id', 'sub_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_[db]');
    }
}
