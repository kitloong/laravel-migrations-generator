<?php

namespace KitLoong\MigrationsGenerator\Migration\Blueprint;

use KitLoong\MigrationsGenerator\Enum\Migrations\Method\SchemaBuilder;
use KitLoong\MigrationsGenerator\Migration\Blueprint\Support\MethodStringHelper;
use KitLoong\MigrationsGenerator\Migration\Blueprint\Support\Stringable;
use KitLoong\MigrationsGenerator\Migration\Enum\Space;
use KitLoong\MigrationsGenerator\Setting;
use KitLoong\MigrationsGenerator\Support\TableName;

/**
 * Create migration lines with `Schema`.
 *
 * eg 1:
 * ```
 * Schema::table('users', function (Blueprint $table) {
 *     // ...
 * });
 * ```
 *
 * eg 2:
 * ```
 * Schema::connection('sqlite')->table('users', function (Blueprint $table) {
 *     // ...
 * });
 * ```
 *
 * eg 3:
 * ```
 * Schema::dropIfExists('users);
 * ```
 */
class SchemaBlueprint implements WritableBlueprint
{
    use Stringable;
    use MethodStringHelper;
    use TableName;

    /**
     * The table name without prefix. {@see \Illuminate\Support\Facades\DB::getTablePrefix()}
     */
    private string $table;

    private ?TableBlueprint $blueprint = null;

    /**
     * SchemaBlueprint constructor.
     *
     * @param  string  $table  Table name.
     * @param  \KitLoong\MigrationsGenerator\Enum\Migrations\Method\SchemaBuilder  $schemaBuilder  SchemaBuilder name.
     */
    public function __construct(string $table, private readonly SchemaBuilder $schemaBuilder)
    {
        $this->table = $this->stripTablePrefix($table);
    }

    public function setBlueprint(TableBlueprint $blueprint): void
    {
        $this->blueprint = $blueprint;
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        $lines = $this->getLines();
        return $this->flattenLines($lines, 2);
    }

    /**
     * @return string[]
     */
    private function getLines(): array
    {
        $schema = $this->connection('Schema', $this->schemaBuilder);

        if ($this->schemaBuilder === SchemaBuilder::DROP_IF_EXISTS) {
            return $this->getDropLines($schema);
        }

        $tableLines = $this->getTableLines($schema);

        if (!app(Setting::class)->isWithHasTable()) {
            return $tableLines;
        }

        $schemaHasTable = $this->connection('Schema', SchemaBuilder::HAS_TABLE);

        $lines = [];

        $lines[] = $this->getIfCondition($schemaHasTable, $this->table);

        foreach ($tableLines as $tableLine) {
            // Add another tabulation to indent(prettify) blueprint definition.
            $lines[] = Space::TAB->value . $tableLine;
        }

        $lines[] = "}";

        return $lines;
    }

    /**
     * Get drop commands in array.
     *
     * @param  string  $schema  The stringify `Schema::something` or `Schema::connection('db')->something`.
     * @return string[]
     */
    private function getDropLines(string $schema): array
    {
        return [
            "$schema('$this->table');",
        ];
    }

    /**
     * Get table commands in array.
     *
     * @param  string  $schema  The stringify `Schema::something` or `Schema::connection('db')->something`.
     * @return string[]
     */
    private function getTableLines(string $schema): array
    {
        if ($this->blueprint === null) {
            return [];
        }

        $lines   = [];
        $lines[] = "$schema('$this->table', function (Blueprint \$table) {";

        if (app(Setting::class)->isWithHasTable()) {
            $this->blueprint->increaseNumberOfPrefixTab();
        }

        // Add 1 tabulation to indent(prettify) blueprint definition.
        $lines[] = Space::TAB->value . $this->blueprint->toString();
        $lines[] = "});";

        return $lines;
    }

    /**
     * Generate `if` condition string.
     *
     * @param  string  $schemaHasTable  The stringify `Schema::hasTable` or `Schema::connection('db')->hasTable`.
     */
    private function getIfCondition(string $schemaHasTable, string $tableWithoutPrefix): string
    {
        if ($this->schemaBuilder === SchemaBuilder::TABLE) {
            return "if ($schemaHasTable('$tableWithoutPrefix')) {";
        }

        return "if (!$schemaHasTable('$tableWithoutPrefix')) {";
    }
}
