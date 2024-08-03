<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use KitLoong\MigrationsGenerator\Enum\Driver;
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
        Schema::create('collations', function (Blueprint $table) {
            $table->charset   = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';

            switch (DB::getDriverName()) {
                case Driver::PGSQL->value:
                    $collation = 'en_US.utf8';
                    break;
                case Driver::SQLSRV->value:
                    $collation = 'Latin1_General_100_CI_AI_SC_UTF8';
                    break;
                case Driver::MARIADB->value:
                case Driver::MYSQL->value:
                    $collation = 'utf8_unicode_ci';
                    break;
                default:
                    $collation = 'BINARY';
            }

            $table->char('char');
            $table->char('char_charset')->charset('utf8');

            // sqlsrv does not support collation with enum
            switch (DB::getDriverName()) {
                case Driver::MYSQL->value:
                case Driver::PGSQL->value:
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

            if (in_array(DB::getDriverName(), [Driver::MARIADB->value, Driver::MYSQL->value])) {
                $table->set('set', ['strawberry', 'vanilla']);
                $table->set('set_default', ['strawberry', 'vanilla'])->default('strawberry');
                $table->set('set_charset', ['strawberry', 'vanilla'])->charset('utf8');
                $table->set('set_collation', ['strawberry', 'vanilla'])->collation($collation);
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
        Schema::dropIfExists('all_columns');
    }
};
