<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

/** @noinspection PhpUnused */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use KitLoong\MigrationsGenerator\Enum\Driver;
use KitLoong\MigrationsGenerator\Support\CheckMigrationMethod;

class ExpectedCreateTestIndex_DB_Table extends Migration
{
    use CheckMigrationMethod;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_index_[db]', function (Blueprint $table) {
            $table->increments('id');
            $table->string('index')->index();
            $table->string('index_custom')->index('index_custom');
            $table->string('col_multi1');
            $table->string('col_multi2');
            $table->string('col_multi_custom1');
            $table->string('col_multi_custom2');
            $table->string('unique')->unique();
            $table->string('unique_custom')->unique('unique_custom');
            $table->string('column-hyphen')->index();
            $table->string('chain')->index();

            $table->index(['col_multi1', 'col_multi2']);
            $table->index(['col_multi_custom1', 'col_multi_custom2'], 'index_multi_custom');
            $table->unique(['col_multi1', 'col_multi2']);
            $table->unique(['col_multi_custom1', 'col_multi_custom2'], 'unique_multi_custom');
            $table->unique('chain');

            // SQLite does not support spatial index.
            if (DB::getDriverName() !== Driver::SQLITE()->getValue()) {
                $table->lineString('spatial_index')->spatialIndex();
                $table->lineString('spatial_index_custom');
                $table->spatialIndex('spatial_index_custom', 'spatial_index_custom');
            }

            if (
                in_array(DB::getDriverName(), [Driver::MYSQL()->getValue(), Driver::PGSQL()->getValue()])
                && $this->hasFullText()
            ) {
                $table->string('fulltext')->fulltext();
                $table->string('fulltext_custom')->fulltext('fulltext_custom');
                $table->fullText(['col_multi1', 'col_multi2']);
                $table->fullText('chain');
            }
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
