<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

/** @noinspection PhpUnused */

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use KitLoong\MigrationsGenerator\Enum\Driver;
use KitLoong\MigrationsGenerator\Support\CheckMigrationMethod;
use KitLoong\MigrationsGenerator\Tests\TestMigration;

class ExpectedCreateAllColumns_DB_Table extends TestMigration
{
    use CheckMigrationMethod;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('all_columns_[db]', function (Blueprint $table) {
            if ($this->hasTableComment()) {
                $table->comment('A table comment.');
            }

            $table->bigInteger('bigInteger');
            $table->bigInteger('bigInteger_default')->default(1080);
            $table->binary('binary');
            $table->boolean('boolean');
            $table->boolean('boolean_default_false')->default(0);
            $table->boolean('boolean_default_true')->default(1);
            $table->boolean('boolean_unsigned')->unsigned();
            $table->char('char');
            $table->char('char_255', 255);
            $table->char('char_100', 100);
            $table->char('char_default')->default('default');
            $table->date('date');
            $table->date('date_default')->default('2020-10-08');
            $table->dateTime('dateTime');
            $table->dateTime('dateTime_0', 0);
            $table->dateTime('dateTime_1', 1);
            $table->dateTime('dateTime_2', 2);
            $table->dateTime('dateTime_3', 3);
            $table->dateTime('dateTime_4', 4);
            $table->dateTime('dateTime_5', 5);
            $table->dateTime('dateTime_default', 2)->default('2020-10-08 10:20:30');
            $table->dateTimeTz('dateTimeTz');
            $table->dateTimeTz('dateTimeTz_0', 0);
            $table->dateTimeTz('dateTimeTz_1', 1);
            $table->dateTimeTz('dateTimeTz_2', 2);
            $table->dateTimeTz('dateTimeTz_3', 3);
            $table->dateTimeTz('dateTimeTz_4', 4);
            $table->dateTimeTz('dateTimeTz_default')->default('2020-10-08 10:20:30');
            $table->decimal('decimal');
            $table->decimal('decimal_82', 8, 2);
            $table->decimal('decimal_83', 8, 3);
            $table->decimal('decimal_92', 9, 2);
            $table->decimal('decimal_53', 5, 3);
            $table->decimal('decimal_default')->default(10.8);
            $table->double('double');
            $table->double('double_default')->default(10.8);
            $table->enum('enum', ['easy', 'hard']);
            $table->enum('enum_default', ['easy', 'hard'])->default('easy');
            $table->float('float');
            $table->float('float_default')->default(10.8);
            $table->integer('integer');
            $table->integer('integer_default')->default(1080);
            $table->ipAddress('ipAddress');
            $table->ipAddress('ipAddress_default')->default('10.0.0.8');
            $table->json('json');
            $table->jsonb('jsonb');
            $table->longText('longText');
            $table->mediumInteger('mediumInteger');
            $table->mediumInteger('mediumInteger_default')->default(1080);
            $table->mediumText('mediumText');
            $table->geometry('geometry');

            // https://github.com/laravel/framework/pull/49634
            if ($this->atLeastLaravel11()) {
                if (
                    DB::getDriverName() !== Driver::MYSQL()->getValue() ||
                    version_compare(DB::getServerVersion(), '5.8', '>')
                ) {
                    $table->geography('geography');
                    $table->geography('geographyGeometryCollection', 'geometryCollection');
                    $table->geography('geographyLineString', 'lineString');
                    $table->geography('geographyMultiLineString', 'multiLineString');
                    $table->geography('geographyMultiPoint', 'multiPoint');
                    $table->geography('geographyMultiPolygon', 'multiPolygon');
                    $table->geography('geographyPoint', 'point');
                    $table->geography('geographyPolygon', 'polygon');
                    $table->geography('geography1', null, 3857);

                    if (DB::getDriverName() !== Driver::PGSQL()->getValue()) {
                        $table->geography('geography2', 'geometryCollection', 3857);
                    }

                    $table->geometry('geometryCollection', 'geometryCollection');
                    $table->geometry('lineString', 'lineString');
                    $table->geometry('multiLineString', 'multiLineString');
                    $table->geometry('multiPoint', 'multiPoint');
                    $table->geometry('multiPolygon', 'multiPolygon');
                    $table->geometry('point', 'point');
                    $table->geometry('polygon', 'polygon');
                    $table->geometry('geometry1', null, 3857);
                    $table->geometry('geometry2', 'geometryCollection', 3857);
                }
            }

            if (!$this->atLeastLaravel11()) {
                $table->geometryCollection('geometryCollection'); // @phpstan-ignore-line
                $table->lineString('lineString');  // @phpstan-ignore-line
                $table->multiLineString('multiLineString');  // @phpstan-ignore-line
                $table->multiPoint('multiPoint');  // @phpstan-ignore-line
                $table->multiPolygon('multiPolygon');  // @phpstan-ignore-line
                $table->point('point');  // @phpstan-ignore-line
                $table->polygon('polygon');  // @phpstan-ignore-line
            }

            $table->smallInteger('smallInteger');
            $table->smallInteger('smallInteger_default')->default(1080);
            $table->string('string');
            $table->string('string_255', 255);
            $table->string('string_100', 100);
            $table->string('string_default_empty')->default('');
            $table->string('string_default_null')->default(null);
            $table->text('text');
            $table->time('time');
            $table->time('time_0', 0);
            $table->time('time_2', 2);
            $table->time('time_default')->default('10:20:30');
            $table->timeTz('timeTz');
            $table->timeTz('timeTz_0', 0);
            $table->timeTz('timeTz_2', 2);
            $table->timeTz('timeTz_default')->default('10:20:30');
            $table->timestamp('timestamp');
            $table->timestamp('timestamp_useCurrent')->useCurrent();
            $table->timestamp('timestamp_useCurrentOnUpdate')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('timestamp_default_useCurrentOnUpdate')->default(
                '2020-10-08 10:20:30'
            )->useCurrentOnUpdate();
            $table->timestampTz('timestampTz_useCurrentOnUpdate')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('timestamp_0', 0)->nullable();
            $table->timestamp('timestamp_2', 2)->nullable();
            $table->timestamp('timestamp_default')->default('2020-10-08 10:20:30');
            $table->timestampTz('timestampTz')->nullable();
            $table->timestampTz('timestampTz_useCurrent')->useCurrent();
            $table->timestampTz('timestampTz_0', 0)->nullable();
            $table->timestampTz('timestampTz_2', 2)->nullable();
            $table->timestampTz('timestampTz_default')->default('2020-10-08 10:20:30');
            $table->tinyInteger('tinyInteger');
            $table->tinyInteger('tinyInteger_default')->default(10);
            $table->unsignedBigInteger('unsignedBigInteger');
            $table->decimal('unsignedDecimal')->unsigned();
            $table->double('unsignedDouble')->unsigned();
            $table->float('unsignedFloat')->unsigned();

            if (method_exists(Blueprint::class, 'unsignedDecimal')) {
                // https://github.com/laravel/framework/pull/48861
                $table->double('double_82', 8, 2); // @phpstan-ignore-line
                $table->double('double_83', 8, 3); // @phpstan-ignore-line
                $table->double('double_92', 9, 2); // @phpstan-ignore-line
                $table->double('double_53', 5, 3); // @phpstan-ignore-line
                $table->float('float_82', 8, 2); // @phpstan-ignore-line
                $table->float('float_83', 8, 3); // @phpstan-ignore-line
                $table->float('float_92', 9, 2); // @phpstan-ignore-line
                $table->float('float_53', 5, 3); // @phpstan-ignore-line
                $table->float('float_56', 50);
            }

            $table->unsignedInteger('unsignedInteger');
            $table->unsignedMediumInteger('unsignedMediumInteger');
            $table->unsignedSmallInteger('unsignedSmallInteger');
            $table->unsignedTinyInteger('unsignedTinyInteger');
            $table->year('year')->default(2020);
            $table->macAddress('macAddress');
            $table->macAddress('macAddress_default')->default('00:0a:95:9d:68:16');
            $table->uuid('uuid');
            $table->uuid('uuid_default')->default('f6a16ff7-4a31-11eb-be7b-8344edc8f36b');
            $table->string('name space')->comment('Test');
            $table->string('test_special_char')
                ->default('string !@#$%^^&*()_+-=[]{};:,./<>?~`| \ \\ \\\ \\\\ \'\' \\\\\'\' " \" \\" \\\" \\\\" quotes')
                ->comment('string !@#$%^^&*()_+-=[]{};:,./<>?~`| \ \\ \\\ \\\\ \'\' \\\\\'\' " \" \\" \\\" \\\\" quotes');

            if ($this->hasTinyText()) {
                $table->tinyText('tinyText');
            }

            if ($this->hasULID()) {
                $table->ulid('ulid');
            }

            switch (DB::getDriverName()) {
                case Driver::MYSQL():
                    if ($this->hasSet()) {
                        $table->set('set', ['strawberry', 'vanilla']);
                    }
                    break;
                default:
            }

            switch (DB::getDriverName()) {
                case Driver::MYSQL():
                    $table->string('virtual')->nullable()->virtualAs('CONCAT(string, " ", string_255)');
                    $table->string('stored')->nullable()->storedAs("CONCAT(string_255, ' ', string)");
                    break;
                case Driver::PGSQL():
                    $table->string('stored')->nullable()->storedAs("string_255 || ' ' || string");
                    break;
                default:
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
        Schema::dropIfExists('all_columns_[db]');
    }
}
