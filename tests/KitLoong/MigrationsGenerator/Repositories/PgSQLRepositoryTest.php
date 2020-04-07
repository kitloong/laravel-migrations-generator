<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/04/07
 */

namespace Tests\KitLoong\MigrationsGenerator\Repositories;

use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\MigrationGeneratorSetting;
use KitLoong\MigrationsGenerator\Repositories\PgSQLRepository;
use Mockery\MockInterface;
use Orchestra\Testbench\TestCase;

class PgSQLRepositoryTest extends TestCase
{
    public function testGetTypeByColumnName()
    {
        $this->mock(MigrationGeneratorSetting::class, function (MockInterface $mock) {
            $mock->shouldReceive('getConnection');
        });

        DB::shouldReceive('connection->select')
            ->with($this->getTypeSql('table', 'column'))
            ->andReturn([
                (object) ['datatype' => "type"]
            ])
            ->once();

        /** @var PgSQLRepository $repository */
        $repository = app(PgSQLRepository::class);
        $type = $repository->getTypeByColumnName('table', 'column');

        $this->assertSame('type', $type);
    }

    public function testGetTypeByColumnNameReturnNull()
    {
        $this->mock(MigrationGeneratorSetting::class, function (MockInterface $mock) {
            $mock->shouldReceive('getConnection');
        });

        DB::shouldReceive('connection->select')
            ->with($this->getTypeSql('table', 'column'))
            ->andReturn([])
            ->once();

        /** @var PgSQLRepository $repository */
        $repository = app(PgSQLRepository::class);
        $type = $repository->getTypeByColumnName('table', 'column');

        $this->assertNull($type);
    }

    private function getTypeSql(string $table, string $column): string
    {
        return "SELECT
    pg_catalog.format_type(a.atttypid, a.atttypmod) as \"datatype\"
FROM
    pg_catalog.pg_attribute a
WHERE
    a.attnum > 0
    AND NOT a.attisdropped
    AND a.attrelid = (
        SELECT c.oid
        FROM pg_catalog.pg_class c
            LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
        WHERE c.relname ~ '^(${table})$'
            AND pg_catalog.pg_table_is_visible(c.oid)
    )
    AND a.attname='${column}'";
    }
}
