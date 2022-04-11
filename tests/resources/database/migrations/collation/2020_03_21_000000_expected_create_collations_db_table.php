<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

/** @noinspection PhpUnused */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use KitLoong\MigrationsGenerator\Enum\Driver;
use KitLoong\MigrationsGenerator\Support\CheckLaravelVersion;

class ExpectedCreateCollations_DB_Table extends Migration
{
    use CheckLaravelVersion;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('collations_[db]', function (Blueprint $table) {
            $table->charset   = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';

            switch (DB::getDriverName()) {
                case Driver::PGSQL():
                    $collation = 'en_US.utf8';
                    break;
                case Driver::SQLSRV():
                    $collation = 'Latin1_General_100_CI_AI_SC_UTF8';
                    break;
                default:
                    $collation = 'utf8_unicode_ci';
            }

            $table->char('char');
            $table->char('char_charset')->charset('utf8');

            // sqlsrv does not support collation with enum
            switch (DB::getDriverName()) {
                case Driver::MYSQL():
                case Driver::PGSQL():
                    $table->enum('enum', ['easy', 'hard']);
                    $table->enum('enum_charset', ['easy', 'hard'])->charset('utf8');
                    $table->enum('enum_collation', ['easy', 'hard'])->collation($collation);
                    break;
                default:
            }

            $table->longText('longText');
            $table->longText('longText_charset')->charset('utf8');
            $table->longText('longText_collation')->collation($collation);
            $table->mediumText('mediumText');
            $table->mediumText('mediumText_charset')->charset('utf8');
            $table->mediumText('mediumText_collation')->collation($collation);
            $table->text('text');
            $table->text('text_charset')->charset('utf8');
            $table->text('text_collation')->collation($collation);

            if (DB::getDriverName() === Driver::MYSQL()->getValue()) {
                if ($this->atLeastLaravel5Dot8()) {
                    $table->set('set', ['strawberry', 'vanilla']);
                    $table->set('set_default', ['strawberry', 'vanilla'])->default('strawberry');
                    $table->set('set_charset', ['strawberry', 'vanilla'])->charset('utf8');
                    $table->set('set_collation', ['strawberry', 'vanilla'])->collation($collation);
                }
            }

            $table->string('string');
            $table->string('string_charset')->charset('utf8');
            $table->string('string_collation')->collation($collation);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('all_columns_[db]');
    }
}
