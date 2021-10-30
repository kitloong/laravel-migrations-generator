<?php

/** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection PhpUnused */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExpectedCreateNameWithHyphen_DB_Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('name-with-hyphen-[db]', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('with-hyphen');
            $table->timestamp('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('name-with-hyphen-[db]');
    }
}
