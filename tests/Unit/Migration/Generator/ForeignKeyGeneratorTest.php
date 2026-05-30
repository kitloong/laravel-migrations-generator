<?php

namespace KitLoong\MigrationsGenerator\Tests\Unit\Migration\Generator;

use KitLoong\MigrationsGenerator\Database\Models\SQLite\SQLiteForeignKey;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\Foreign;
use KitLoong\MigrationsGenerator\Migration\Blueprint\Method;
use KitLoong\MigrationsGenerator\Migration\Generator\ForeignKeyGenerator;
use KitLoong\MigrationsGenerator\Setting;
use KitLoong\MigrationsGenerator\Support\CheckLaravelVersion;
use KitLoong\MigrationsGenerator\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ForeignKeyGeneratorTest extends TestCase
{
    use CheckLaravelVersion;

    public function testGenerateDropWithNullName(): void
    {
        $setting = app(Setting::class);
        $setting->setIgnoreForeignKeyNames(false);

        $foreignKeyGenerator = app(ForeignKeyGenerator::class);

        $method = $foreignKeyGenerator->generateDrop(new SQLiteForeignKey('table', [
            'name'            => null,
            'columns'         => ['column'],
            'foreign_schema'  => null,
            'foreign_table'   => 'foreign_table',
            'foreign_columns' => ['foreign_column'],
            'on_update'       => 'on_update',
            'on_delete'       => 'on_delete',
        ]));

        $this->assertSame($method->getName(), Foreign::DROP_FOREIGN);
        $this->assertEmpty($method->getValues());
    }

    #[DataProvider('schemaDataProvider')]
    public function testGenerateOnTableName(?string $currentSchema, ?string $foreignSchema, string $expectedOnValue): void
    {
        $setting = app(Setting::class);
        $setting->setIgnoreForeignKeyNames(false);
        $setting->setCurrentSchema($currentSchema);

        $foreignKey = new SQLiteForeignKey('table', [
            'name'            => null,
            'columns'         => ['column'],
            'foreign_schema'  => $foreignSchema,
            'foreign_table'   => 'foreign_table',
            'foreign_columns' => ['foreign_column'],
            'on_update'       => 'on_update',
            'on_delete'       => 'on_delete',
        ]);

        $method = app(ForeignKeyGenerator::class)->generate($foreignKey);

        $onChain = collect($method->getChains())->first(static fn ($chain) => $chain->getName() === Foreign::ON);

        $this->assertInstanceOf(Method::class, $onChain);
        $this->assertSame([$expectedOnValue], $onChain->getValues());
    }

    /**
     * @return array<string, array{0: string|null, 1: string|null, 2: string}>
     */
    public static function schemaDataProvider(): array
    {
        return [
            'both schema null'           => [null, null, 'foreign_table'],
            'only current schema exists' => ['public', null, 'foreign_table'],
            'only foreign schema exists' => [null, 'other_schema', 'foreign_table'],
            'same schema'                => ['public', 'public', 'foreign_table'],
            'different schema'           => ['public', 'other_schema', 'other_schema.foreign_table'],
        ];
    }
}
