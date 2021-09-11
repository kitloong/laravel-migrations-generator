<?php

namespace KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Table;
use Illuminate\Support\Str;
use KitLoong\MigrationsGenerator\Generators\Blueprint\SchemaBlueprint;
use KitLoong\MigrationsGenerator\Generators\Blueprint\TableBlueprint;
use KitLoong\MigrationsGenerator\Generators\Methods\SchemaBuilder;
use KitLoong\MigrationsGenerator\Generators\Writer\MigrationWriter;
use KitLoong\MigrationsGenerator\MigrationMethod\TableProperty;
use KitLoong\MigrationsGenerator\MigrationsGeneratorSetting;

class Generator
{
    private $migrationWriter;
    private $columnGenerator;
    private $filenameGenerator;
    private $indexGenerator;
    private $setting;

    public function __construct(
        MigrationWriter $migrationWriter,
        ColumnGenerator $columnGenerator,
        FilenameGenerator $filenameGenerator,
        IndexGenerator $indexGenerator,
        MigrationsGeneratorSetting $setting
    ) {
        $this->migrationWriter   = $migrationWriter;
        $this->columnGenerator   = $columnGenerator;
        $this->filenameGenerator = $filenameGenerator;
        $this->indexGenerator    = $indexGenerator;
        $this->setting           = $setting;
    }

    /**
     * @param  \Doctrine\DBAL\Schema\Table  $table
     * @param  \Doctrine\DBAL\Schema\Column[]  $columns
     * @return string file path
     */
    public function generateTable(Table $table, array $columns): string
    {
        $blueprint = new TableBlueprint();

        if ($this->shouldSetCharset()) {
            $blueprint = $this->setTableCharset($blueprint, $table);
            $blueprint->setLineBreak();
        }

        $this->indexGenerator->setSpatialFlag($table->getIndexes(), $table->getName());
        $singleColumnIndexes = $this->indexGenerator->getSingleColumnIndexes($table->getIndexes());
        $multiColumnsIndexes = $this->indexGenerator->getMultiColumnsIndexes($table->getIndexes());

        foreach ($columns as $column) {
            $columnMethod = $this->columnGenerator->generate($table, $column, $singleColumnIndexes);

            // Some columns can be merged with existing columns in the blueprint.
            // We need to provide the merge name list for blueprint to remove the previous lines.
            if ($columnMethod->hasMergeColumns()) {
                $blueprint->removeLinesByColumnNames($columnMethod->getMergeColumns());
            }
            $blueprint->setColumnMethod($columnMethod);
        }

        foreach ($multiColumnsIndexes as $index) {
            $columnMethod = $this->indexGenerator->generate($index);
            $blueprint->setColumnMethod($columnMethod);
        }

        $up = new SchemaBlueprint($this->setting->getConnection()->getName(), $table->getName(), SchemaBuilder::CREATE);
        $up->setBlueprint($blueprint);
        $down      = new SchemaBlueprint(
            $this->setting->getConnection()->getName(),
            $table->getName(),
            SchemaBuilder::DROP_IF_EXISTS
        );
        $path      = $this->filenameGenerator->generateCreatePath($table->getName());
        $className = $this->filenameGenerator->generateCreateClassName($table->getName());

        $this->migrationWriter->writeTo(
            $path,
            $this->setting->getStubPath(),
            $className,
            $up,
            $down
        );

        return $path;
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
}
