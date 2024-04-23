<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

/** @noinspection PhpUnused */

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
        Schema::create('user_profile', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('user_id_fk_custom')->unsigned();
            $table->bigInteger('user_id_fk_constraint')->unsigned();
            $table->unsignedBigInteger('user_sub_id');
            $table->unsignedBigInteger('user_sub_id_fk_custom');
            $table->unsignedInteger('sub_id');
            $table->string('phone');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('user_id_fk_custom', 'users_foreign_custom')->references('id')->on('users');
            $table->foreign('user_id_fk_constraint', 'users_foreign_constraint')->references('id')->on(
                'users'
            )->onDelete('cascade')->onUpdate('cascade');
            $table->foreign(['user_id', 'user_sub_id'])->references(['id', 'sub_id'])->on('users');
            $table->foreign(['user_id', 'user_sub_id_fk_custom'], 'users_composite_foreign_custom')
                ->references(['id', 'sub_id'])
                ->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_profile');
    }
};
