<?php

namespace KitLoong\MigrationsGenerator\Tests\Unit\Database\Models\PgSQL;

use KitLoong\MigrationsGenerator\Database\Models\PgSQL\PgSQLColumn;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType;
use KitLoong\MigrationsGenerator\Repositories\PgSQLRepository;
use KitLoong\MigrationsGenerator\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;

class PgSQLColumnTest extends TestCase
{
    #[DataProvider('spatialTypeNameProvider')]
    public function testSpatialTypeName(string $type): void
    {
        $this->mock(PgSQLRepository::class, static function (MockInterface $mock): void {
            $mock->shouldReceive('getStoredDefinition');
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

    /**
     * @param string[] $expectedValues
     */
    #[DataProvider('parseEnumValuesFromConstraintProvider')]
    public function testParseEnumValuesFromConstraint(string $constraintDefinition, array $expectedValues): void
    {
        $this->mock(PgSQLRepository::class, static function (MockInterface $mock): void {
            $mock->shouldReceive('getCheckConstraintDefinition')->andReturn('');
        });

        $column = new PgSQLColumn('test_table', [
            'name'           => 'status',
            'type_name'      => 'varchar',
            'type'           => 'character varying(255)',
            'collation'      => null,
            'nullable'       => false,
            'default'        => null,
            'auto_increment' => false,
            'comment'        => null,
            'generation'     => null,
        ]);

        // Use reflection to access the private method
        $reflection = new ReflectionClass($column);
        $method     = $reflection->getMethod('parseEnumValuesFromConstraint');
        $method->setAccessible(true);

        $result = $method->invoke($column, $constraintDefinition);

        $this->assertSame($expectedValues, $result);
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

    /**
     * @return array<string, array{string, string[]}>
     */
    public static function parseEnumValuesFromConstraintProvider(): array
    {
        return [
            // Pattern 1: ANY with ARRAY pattern (most common in PostgreSQL)
            'ANY ARRAY with character varying'                                     => [
                "CHECK ((status)::text = ANY ((ARRAY['active'::character varying, 'inactive'::character varying, 'pending'::character varying])::text[]))",
                ['active', 'inactive', 'pending'],
            ],
            'ANY ARRAY with text'                                                  => [
                "CHECK ((status)::text = ANY ((ARRAY['draft'::text, 'published'::text])::text[]))",
                ['draft', 'published'],
            ],
            'ANY ARRAY with mixed types'                                           => [
                "CHECK ((priority)::text = ANY ((ARRAY['low'::character varying, 'medium'::text, 'high'::character varying])::text[]))",
                ['low', 'medium', 'high'],
            ],
            'ANY ARRAY case insensitive'                                           => [
                "check ((status)::text = any ((array['Active'::character varying, 'INACTIVE'::character varying])::text[]))",
                ['Active', 'INACTIVE'],
            ],
            'ANY ARRAY with extra spaces'                                          => [
                "CHECK ( ( status )::text = ANY ( ( ARRAY[ 'option1'::character varying , 'option2'::character varying ] )::text[] ) )",
                ['option1', 'option2'],
            ],

            // Pattern 2: Multiple OR conditions
            'OR conditions basic'                                                  => [
                "CHECK (((status)::text = 'active'::text) OR ((status)::text = 'inactive'::text))",
                ['active', 'inactive'],
            ],
            'OR conditions with more values'                                       => [
                "CHECK (((status)::text = 'draft'::text) OR ((status)::text = 'review'::text) OR ((status)::text = 'published'::text))",
                ['draft', 'review', 'published'],
            ],
            'OR conditions case insensitive'                                       => [
                "check (((status)::text = 'ACTIVE'::text) or ((status)::text = 'inactive'::text))",
                ['ACTIVE', 'inactive'],
            ],
            'OR conditions with character varying'                                 => [
                "CHECK (((status)::character varying = 'yes'::character varying) OR ((status)::character varying = 'no'::character varying))",
                ['yes', 'no'],
            ],
            'OR conditions with duplicates'                                        => [
                "CHECK (((status)::text = 'active'::text) OR ((status)::text = 'inactive'::text) OR ((status)::text = 'active'::text))",
                ['active', 'inactive'], // Should remove duplicates
            ],

            // Pattern 3: Simple IN clause
            'IN clause basic'                                                      => [
                "CHECK (status IN ('active', 'inactive', 'pending'))",
                ['active', 'inactive', 'pending'],
            ],
            'IN clause case insensitive'                                           => [
                "check (status in ('YES', 'no', 'Maybe'))",
                ['YES', 'no', 'Maybe'],
            ],
            'IN clause with extra spaces'                                          => [
                "CHECK ( status IN ( 'option1' , 'option2' , 'option3' ) )",
                ['option1', 'option2', 'option3'],
            ],
            'IN clause single value'                                               => [
                "CHECK (status IN ('single'))",
                ['single'],
            ],

            // Edge cases and invalid patterns
            "CHECK (((string IS NULL) OR ((string)::text ~ '^O\.[0-9]+$'::text)))" => [
                '',
                [],
            ],
            'empty constraint'                                                     => [
                '',
                [],
            ],
            'non-enum constraint'                                                  => [
                "CHECK (age > 18)",
                [],
            ],
            'malformed ARRAY pattern'                                              => [
                "CHECK ((status)::text = ANY (ARRAY[missing quotes]))",
                [],
            ],
            'different column name in OR'                                          => [
                "CHECK (((other_column)::text = 'value1'::text) OR ((other_column)::text = 'value2'::text))",
                [],
            ],
            'different column name in IN'                                          => [
                "CHECK (other_column IN ('value1', 'value2'))",
                [],
            ],
            'no values in ARRAY'                                                   => [
                "CHECK ((status)::text = ANY ((ARRAY[])::text[]))",
                [],
            ],
        ];
    }
}
