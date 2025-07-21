<?php

namespace KitLoong\MigrationsGenerator\Tests\Unit\Database\Models;

use Illuminate\Support\Facades\App;
use KitLoong\MigrationsGenerator\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class DatabaseColumnTest extends TestCase
{
    #[DataProvider('escapeDefaultVersionProvider')]
    public function testEscapeDefaultBasedOnLaravelVersion(
        string $laravelVersion,
        ?string $input,
        ?string $expected,
    ): void {
        // Mock Laravel version
        App::shouldReceive('version')
            ->andReturn($laravelVersion);

        $column = new TestDatabaseColumn('test_table', [
            'name'           => 'test_column',
            'type_name'      => 'varchar',
            'type'           => 'varchar(255)',
            'collation'      => null,
            'nullable'       => true,
            'default'        => null,
            'auto_increment' => false,
            'comment'        => null,
            'generation'     => null,
        ]);

        $result = $column->testEscapeDefault($input);
        $this->assertSame($expected, $result);
    }

    public function testEscapeDefaultWithComplexStrings(): void
    {
        App::shouldReceive('version')->andReturn('11.0.0'); // Old version

        $column = new TestDatabaseColumn('test_table', [
            'name'           => 'test_column',
            'type_name'      => 'varchar',
            'type'           => 'varchar(255)',
            'collation'      => null,
            'nullable'       => true,
            'default'        => null,
            'auto_increment' => false,
            'comment'        => null,
            'generation'     => null,
        ]);

        // Test complex string with multiple characters to escape
        $input  = "string !@#$%^^&*()_+-=[]{};:,./<>?~`| \\ \\' \\\\\' \" \\\" \\\\\"";
        $result = $column->testEscapeDefault($input);

        // Should escape both single quotes and backslashes for old Laravel versions
        $expected = "string !@#$%^^&*()_+-=[]{};:,./<>?~`| \\\\ \\\\'' \\\\\\\\\\\\'' \" \\\\\" \\\\\\\\\"";
        $this->assertSame($expected, $result);
    }

    public function testEscapeDefaultWithNewLaravelVersionComplexString(): void
    {
        App::shouldReceive('version')->andReturn('12.20.0'); // New version

        $column = new TestDatabaseColumn('test_table', [
            'name'           => 'test_column',
            'type_name'      => 'varchar',
            'type'           => 'varchar(255)',
            'collation'      => null,
            'nullable'       => true,
            'default'        => null,
            'auto_increment' => false,
            'comment'        => null,
            'generation'     => null,
        ]);

        // Test complex string - should only escape backslashes, not quotes
        $input  = "string !@#$%^^&*()_+-=[]{};:,./<>?~`| \\ \\' \\\\\' \" \\\" \\\\\"";
        $result = $column->testEscapeDefault($input);

        // Should only escape backslashes for new Laravel versions
        $expected = "string !@#$%^^&*()_+-=[]{};:,./<>?~`| \\\\ \\\\' \\\\\\\\\\\\' \" \\\\\" \\\\\\\\\"";
        $this->assertSame($expected, $result);
    }

    /**
     * @return array<string, array{string, string|null, string|null}>
     */
    public static function escapeDefaultVersionProvider(): array
    {
        return [
            // Laravel < 12.20.0 cases - should escape single quotes
            'Laravel 11.x.x with single quotes'                => [
                '11.5.0',
                "test'value",
                "test''value",
            ],
            'Laravel 12.19.x with single quotes'               => [
                '12.19.9',
                "value with 'quotes' inside",
                "value with ''quotes'' inside",
            ],
            'Laravel 12.19.x with backslashes'                 => [
                '12.19.9',
                'path\\to\\file',
                'path\\\\to\\\\file',
            ],
            'Laravel 12.19.x with both quotes and backslashes' => [
                '12.19.9',
                "path\\to'file'",
                "path\\\\to''file''",
            ],
            'Laravel 12.19.x with null input'                  => [
                '12.19.9',
                null,
                null,
            ],

            // Laravel >= 12.20.0 cases - should NOT escape single quotes
            'Laravel 12.20.0 with single quotes'               => [
                '12.20.0',
                "test'value",
                "test'value",
            ],
            'Laravel 12.21.x with single quotes'               => [
                '12.21.5',
                "value with 'quotes' inside",
                "value with 'quotes' inside",
            ],
            'Laravel 13.x.x with single quotes'                => [
                '13.0.0',
                "test'value",
                "test'value",
            ],
            'Laravel 12.20.0 with backslashes'                 => [
                '12.20.0',
                'path\\to\\file',
                'path\\\\to\\\\file',
            ],
            'Laravel 12.20.0 with both quotes and backslashes' => [
                '12.20.0',
                "path\\to'file'",
                "path\\\\to'file'",
            ],
            'Laravel 12.20.0 with null input'                  => [
                '12.20.0',
                null,
                null,
            ],
        ];
    }
}
