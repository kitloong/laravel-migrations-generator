<?php

namespace KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Table;
use Illuminate\Support\Str;
use KitLoong\MigrationsGenerator\DBAL\Platform;
use KitLoong\MigrationsGenerator\Generators\Blueprint\SchemaBlueprint;
use KitLoong\MigrationsGenerator\Generators\Blueprint\TableBlueprint;
use KitLoong\MigrationsGenerator\Generators\MigrationConstants\Method\SchemaBuilder;
use KitLoong\MigrationsGenerator\Generators\MigrationConstants\Property\TableProperty;
use KitLoong\MigrationsGenerator\MigrationsGeneratorSetting;

class TableMigration
{
    private $columnGenerator;
    private $indexGenerator;
    private $setting;

    public function __construct(
        ColumnGenerator $columnGenerator,
        IndexGenerator $indexGenerator,
        MigrationsGeneratorSetting $setting
    ) {
        $this->columnGenerator = $columnGenerator;
        $this->indexGenerator  = $indexGenerator;
        $this->setting         = $setting;
    }

    /**
     * @param  \Doctrine\DBAL\Schema\Table  $table
     * @param  \Doctrine\DBAL\Schema\Column[]  $columns
     * @param  \Doctrine\DBAL\Schema\Index[]  $indexes
     * @return \KitLoong\MigrationsGenerator\Generators\Blueprint\SchemaBlueprint
     */
    public function up(Table $table, array $columns, array $indexes): SchemaBlueprint
    {
        $up = $this->getSchemaBlueprint($table, SchemaBuilder::CREATE);

        $blueprint = new TableBlueprint();

        if ($this->shouldSetCharset()) {
            $blueprint = $this->setTableCharset($blueprint, $table);
            $blueprint->setLineBreak();
        }

        // Example
        // $table->foreign('user_id')->references(['id'])->on('users_mysql57');
        // $table->foreign(['user_id', 'sub_id'])->references(['id', 'sub_id'])->on('users_mysql57');
        // $table->getIndexes() will return extra "IDX_*" index for column user_id
        // Use $indexes instead.
        $this->indexGenerator->setSpatialFlag($indexes, $table->getName());
        $singleColumnIndexes = $this->indexGenerator->getSingleColumnIndexes($indexes);
        $multiColumnsIndexes = $this->indexGenerator->getMultiColumnsIndexes($indexes);

        foreach ($columns as $column) {
            $columnMethod = $this->columnGenerator->generate($table, $column, $singleColumnIndexes);
            $blueprint->setColumnMethod($columnMethod);
        }

        $blueprint->mergeTimestamps();

        if ($multiColumnsIndexes->isNotEmpty()) {
            $blueprint->setLineBreak();
            foreach ($multiColumnsIndexes as $index) {
                $columnMethod = $this->indexGenerator->generate($table, $index);
                $blueprint->setColumnMethod($columnMethod);
            }
        }

        $up->setBlueprint($blueprint);

        return $up;
    }

    public function down(Table $table): SchemaBlueprint
    {
        return $this->getSchemaBlueprint($table, SchemaBuilder::DROP_IF_EXISTS);
    }

    private function shouldSetCharset(): bool
    {
        if ($this->setting->getPlatform() !== Platform::MYSQL) {
            return false;
        }

        return $this->setting->isUseDBCollation();
    }

    private function setTableCharset(TableBlueprint $blueprint, Table $table): TableBlueprint
    {
        $blueprint->setProperty(
            TableProperty::COLLATION,
            $collation = $table->getOptions()['collation']
        );

        $charset = Str::before($collation, '_');
        $blueprint->setProperty(TableProperty::CHARSET, $charset);

        return $blueprint;
    }

    /**
     * @param  \Doctrine\DBAL\Schema\Table  $table
     * @param  string  $schemaBuilder
     * @return \KitLoong\MigrationsGenerator\Generators\Blueprint\SchemaBlueprint
     */
    private function getSchemaBlueprint(Table $table, string $schemaBuilder): SchemaBlueprint
    {
        return new SchemaBlueprint(
            $this->setting->getConnection()->getName(),
            $table->getName(),
            $schemaBuilder
        );
    }
}
