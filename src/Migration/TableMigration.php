<?php

namespace KitLoong\MigrationsGenerator\Migration;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use KitLoong\MigrationsGenerator\Enum\Driver;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\SchemaBuilder;
use KitLoong\MigrationsGenerator\Enum\Migrations\Property\TableProperty;
use KitLoong\MigrationsGenerator\Migration\Blueprint\SchemaBlueprint;
use KitLoong\MigrationsGenerator\Migration\Blueprint\TableBlueprint;
use KitLoong\MigrationsGenerator\Migration\Generator\ColumnGenerator;
use KitLoong\MigrationsGenerator\Migration\Generator\IndexGenerator;
use KitLoong\MigrationsGenerator\Schema\Models\Table;
use KitLoong\MigrationsGenerator\Setting;

class TableMigration
{
    private $columnGenerator;
    private $indexGenerator;
    private $setting;

    public function __construct(
        ColumnGenerator $columnGenerator,
        IndexGenerator $indexGenerator,
        Setting $setting
    ) {
        $this->columnGenerator = $columnGenerator;
        $this->indexGenerator  = $indexGenerator;
        $this->setting         = $setting;
    }

    /**
     * Generates `up` schema for table.
     *
     * @param  \KitLoong\MigrationsGenerator\Schema\Models\Table  $table
     * @return \KitLoong\MigrationsGenerator\Migration\Blueprint\SchemaBlueprint
     */
    public function up(Table $table): SchemaBlueprint
    {
        $up = $this->getSchemaBlueprint($table, SchemaBuilder::CREATE());

        $blueprint = new TableBlueprint();

        if ($this->shouldSetCharset()) {
            $blueprint = $this->setTableCharset($blueprint, $table);
            $blueprint->setLineBreak();
        }

        $chainableIndexes    = $this->indexGenerator->getChainableIndexes($table->getName(), $table->getIndexes());
        $notChainableIndexes = $this->indexGenerator->getNotChainableIndexes($table->getIndexes(), $chainableIndexes);

        foreach ($table->getColumns() as $column) {
            $method = $this->columnGenerator->generate($table, $column, $chainableIndexes);
            $blueprint->setMethod($method);
        }

        $blueprint->mergeTimestamps();

        if ($notChainableIndexes->isNotEmpty()) {
            $blueprint->setLineBreak();
            foreach ($notChainableIndexes as $index) {
                $method = $this->indexGenerator->generate($table, $index);
                $blueprint->setMethod($method);
            }
        }

        $up->setBlueprint($blueprint);

        return $up;
    }

    /**
     * Generates `down` schema for table.
     *
     * @param  \KitLoong\MigrationsGenerator\Schema\Models\Table  $table
     * @return \KitLoong\MigrationsGenerator\Migration\Blueprint\SchemaBlueprint
     */
    public function down(Table $table): SchemaBlueprint
    {
        return $this->getSchemaBlueprint($table, SchemaBuilder::DROP_IF_EXISTS());
    }

    /**
     * Checks should set charset into table.
     *
     * @return bool
     */
    private function shouldSetCharset(): bool
    {
        if (DB::getDriverName() !== Driver::MYSQL()->getValue()) {
            return false;
        }

        return $this->setting->isUseDBCollation();
    }

    /**
     * @param  \KitLoong\MigrationsGenerator\Migration\Blueprint\TableBlueprint  $blueprint
     * @param  \KitLoong\MigrationsGenerator\Schema\Models\Table  $table
     * @return \KitLoong\MigrationsGenerator\Migration\Blueprint\TableBlueprint
     */
    private function setTableCharset(TableBlueprint $blueprint, Table $table): TableBlueprint
    {
        $blueprint->setProperty(
            TableProperty::COLLATION(),
            $collation = $table->getCollation()
        );

        $charset = Str::before($collation, '_');
        $blueprint->setProperty(TableProperty::CHARSET(), $charset);

        return $blueprint;
    }

    /**
     * @param  \KitLoong\MigrationsGenerator\Schema\Models\Table  $table
     * @param  \KitLoong\MigrationsGenerator\Enum\Migrations\Method\SchemaBuilder  $schemaBuilder
     * @return \KitLoong\MigrationsGenerator\Migration\Blueprint\SchemaBlueprint
     */
    private function getSchemaBlueprint(Table $table, SchemaBuilder $schemaBuilder): SchemaBlueprint
    {
        return new SchemaBlueprint(
            DB::getName(),
            $table->getName(),
            $schemaBuilder
        );
    }
}
