<?php

namespace KitLoong\MigrationsGenerator\Tests\Unit\Database\Models\PgSQL;

use KitLoong\MigrationsGenerator\Database\Models\PgSQL\PgSQLColumn;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType;
use KitLoong\MigrationsGenerator\Repositories\PgSQLRepository;
use KitLoong\MigrationsGenerator\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;

class PgSQLColumnTest extends TestCase
{
    #[DataProvider('spatialTypeNameProvider')]
    public function testSpatialTypeName(string $type): void
    {
        $this->mock(PgSQLRepository::class, static function (MockInterface $mock): void {
            $mock->shouldReceive('getCheckConstraintDefinition')->andReturn(null);
        });

        $column = new PgSQLColumn('table', [
            'name'           => 'column',
            'type_name'      => 'geography',
            'type'           => $type,
            'collation'      => null,
            'nullable'       => false,
            'default'        => null,
            'auto_increment' => false,
            'comment'        => null,
            'generation'     => null,
        ]);

        $this->assertSame(ColumnType::GEOGRAPHY, $column->getType());
        $this->assertSame('point', $column->getSpatialSubType());
        $this->assertSame(4326, $column->getSpatialSrID());
    }

    public function testRegexCheckConstraintShouldNotBecomeEnum(): void
    {
        $this->mock(PgSQLRepository::class, static function (MockInterface $mock): void {
            // Mock a regex-based check constraint using ~ operator
            $mock->shouldReceive('getCheckConstraintDefinition')
                ->with('organisations', 'parent_id')
                ->andReturn("(parent_id IS NULL) OR ((parent_id)::text ~ '^O\\.[0-9]+$'::text)");
        });

        $column = new PgSQLColumn('organisations', [
            'name'           => 'parent_id',
            'type_name'      => 'character varying',
            'type'           => 'character varying',
            'collation'      => null,
            'nullable'       => true,
            'default'        => null,
            'auto_increment' => false,
            'comment'        => null,
            'generation'     => null,
        ]);

        // Should remain STRING type, not become ENUM
        $this->assertSame(ColumnType::STRING, $column->getType());
        $this->assertEmpty($column->getPresetValues());
    }

    public function testEnumCheckConstraintShouldBecomeEnum(): void
    {
        $this->mock(PgSQLRepository::class, static function (MockInterface $mock): void {
            // Mock a real enum-style check constraint
            $mock->shouldReceive('getCheckConstraintDefinition')
                ->with('test_table', 'status')
                ->andReturn("((status)::text = ANY ((ARRAY['pending'::character varying, 'active'::character varying, 'inactive'::character varying])::text[]))");
        });

        $column = new PgSQLColumn('test_table', [
            'name'           => 'status',
            'type_name'      => 'character varying',
            'type'           => 'character varying',
            'collation'      => null,
            'nullable'       => false,
            'default'        => null,
            'auto_increment' => false,
            'comment'        => null,
            'generation'     => null,
        ]);

        // This should become ENUM type
        $this->assertSame(ColumnType::ENUM, $column->getType());
        $this->assertSame(['pending', 'active', 'inactive'], $column->getPresetValues());
    }

    /**
     * @return array<string, string[]>
     */
    public static function spatialTypeNameProvider(): array
    {
        return [
            'with dot'    => ['extensions.geography(Point,4326)'],
            'without dot' => ['geography(Point,4326)'],
        ];
    }
}
