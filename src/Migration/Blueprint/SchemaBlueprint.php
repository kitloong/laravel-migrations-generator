<?php

namespace KitLoong\MigrationsGenerator\Migration\Blueprint;

use KitLoong\MigrationsGenerator\Enum\Migrations\Method\SchemaBuilder;
use KitLoong\MigrationsGenerator\Migration\Enum\Space;
use KitLoong\MigrationsGenerator\Setting;
use KitLoong\MigrationsGenerator\Support\TableName;

class SchemaBlueprint implements WritableBlueprint
{
    use Stringable;
    use TableName;

    private $table;
    private $connection;
    private $schemaBuilder;

    /** @var \KitLoong\MigrationsGenerator\Migration\Blueprint\TableBlueprint|null */
    private $blueprint;

    /**
     * SchemaBlueprint constructor.
     *
     * @param  string  $connection  Connection name.
     * @param  string  $table  Table name.
     * @param  \KitLoong\MigrationsGenerator\Enum\Migrations\Method\SchemaBuilder  $schemaBuilder  SchemaBuilder name.
     */
    public function __construct(string $connection, string $table, SchemaBuilder $schemaBuilder)
    {
        $this->connection    = $connection;
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
        $setting = app(Setting::class);

        $schema = "Schema::$this->schemaBuilder";
        if ($this->connection !== $setting->getDefaultConnection()) {
            $schema = "Schema::" . SchemaBuilder::CONNECTION() . "('$this->connection')->$this->schemaBuilder";
        }

        $tableWithoutPrefix = $this->stripPrefix($this->table);

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
