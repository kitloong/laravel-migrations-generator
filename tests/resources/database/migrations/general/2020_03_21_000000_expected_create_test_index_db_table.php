<?php

/** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection PhpUnused */

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
            $table->string('code', 50)->index();
            $table->string('custom_name', 50)->index('custom_index');
            $table->string('email')->unique();
            $table->string('column-hyphen')->index();
            $table->enum('enum', ['PROGRESS', 'DONE']);
            $table->lineString('line_string')->spatialIndex();
            $table->timestamp('created_at')->nullable();

            $table->primary('id');
            $table->index(['code', 'enum']);
            $table->index(['code', 'email'], 'custom_multi_key_index');
            $table->unique(['enum', 'code']);
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
