<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExpectedCreateTestIndex_DB_Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_index_[db]', function (Blueprint $table) {
            $table->integer('id');
            $table->string('code', 50);
            $table->string('email')->unique();
            $table->enum('enum', ['PROGRESS', 'DONE']);
            $table->geometry('geometry');
            $table->timestamps(2);

            $table->primary('id');

            $table->index('enum', 'user_pro file\'d');
            $table->index('code');
            $table->index(['code', 'enum']);
            $table->index(['enum', 'code']);
            $table->spatialIndex('geometry');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('test_index_[db]');
    }
}
