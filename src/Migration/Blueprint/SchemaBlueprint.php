<?php

namespace KitLoong\MigrationsGenerator\Migration\Blueprint;

use KitLoong\MigrationsGenerator\Enum\Migrations\Method\SchemaBuilder;
use KitLoong\MigrationsGenerator\Migration\Blueprint\Support\MethodStringHelper;
use KitLoong\MigrationsGenerator\Migration\Blueprint\Support\Stringable;
use KitLoong\MigrationsGenerator\Migration\Enum\Space;
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
     * @var string
     */
    private $table;

    /**
     * @var \KitLoong\MigrationsGenerator\Enum\Migrations\Method\SchemaBuilder
     */
    private $schemaBuilder;

    /** @var \KitLoong\MigrationsGenerator\Migration\Blueprint\TableBlueprint|null */
    private $blueprint;

    /**
     * SchemaBlueprint constructor.
     *
     * @param  string  $table  Table name.
     * @param  \KitLoong\MigrationsGenerator\Enum\Migrations\Method\SchemaBuilder  $schemaBuilder  SchemaBuilder name.
     */
    public function __construct(string $table, SchemaBuilder $schemaBuilder)
    {
        $this->table         = $table;
        $this->schemaBuilder = $schemaBuilder;
        $this->blueprint     = null;
    }

    /**
     * @param  \KitLoong\MigrationsGenerator\Migration\Blueprint\TableBlueprint  $blueprint
     */
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

        $tableWithoutPrefix = $this->stripTablePrefix($this->table);

        $lines = [];

        if ($this->blueprint !== null) {
            $lines[] = "$schema('$tableWithoutPrefix', function (Blueprint \$table) {";
            // Add 1 tabulation to indent(prettify) blueprint definition.
            $lines[] = Space::TAB() . $this->blueprint->toString();
            $lines[] = "});";
            return $lines;
        }

        $lines[] = "$schema('$tableWithoutPrefix');";
        return $lines;
    }
}
