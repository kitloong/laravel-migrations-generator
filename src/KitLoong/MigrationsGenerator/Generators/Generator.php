<?php

namespace KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Table;
use KitLoong\MigrationsGenerator\Generators\Blueprint\SchemaBlueprint;
use KitLoong\MigrationsGenerator\Generators\Writer\MigrationWriter;
use KitLoong\MigrationsGenerator\MigrationsGeneratorSetting;

class Generator
{
    private $migrationWriter;
    private $filenameGenerator;
    private $foreignKeyMigration;
    private $tableMigration;
    private $setting;

    public function __construct(
        MigrationWriter $migrationWriter,
        FilenameGenerator $filenameGenerator,
        ForeignKeyMigration $foreignKeyMigration,
        TableMigration $tableMigration,
        MigrationsGeneratorSetting $setting
    ) {
        $this->migrationWriter     = $migrationWriter;
        $this->filenameGenerator   = $filenameGenerator;
        $this->foreignKeyMigration = $foreignKeyMigration;
        $this->tableMigration      = $tableMigration;
        $this->setting             = $setting;
    }

    /**
     * @param  \Doctrine\DBAL\Schema\Table  $table
     * @param  \Doctrine\DBAL\Schema\Column[]  $columns
     * @param  \Doctrine\DBAL\Schema\Index[]  $indexes
     * @return string file path
     */
    public function generateTable(Table $table, array $columns, array $indexes): string
    {
        $up = $this->tableMigration->up($table, $columns, $indexes);

        $down = $this->tableMigration->down($table);

        if (app(MigrationsGeneratorSetting::class)->isSquash()) {
            $this->migrationWriter->writeToTemp($up, $down);
            return '';
        } else {
            return $this->writeMigration(
                $this->filenameGenerator->makeCreatePath($table->getName()),
                $this->filenameGenerator->makeCreateClassName($table->getName()),
                $up,
                $down
            );
        }
    }

    /**
     * @param  \Doctrine\DBAL\Schema\Table  $table
     * @param  \Doctrine\DBAL\Schema\ForeignKeyConstraint[]  $foreignKeys
     * @return string file path
     */
    public function generateForeignKeys(Table $table, array $foreignKeys): string
    {
        $up = $this->foreignKeyMigration->up($table, $foreignKeys);

        $down = $this->foreignKeyMigration->down($table, $foreignKeys);

        if (app(MigrationsGeneratorSetting::class)->isSquash()) {
            $this->migrationWriter->writeToTemp($up, $down);
            return '';
        } else {
            return $this->writeMigration(
                $this->filenameGenerator->makeForeignKeyPath($table->getName()),
                $this->filenameGenerator->makeForeignKeyClassName($table->getName()),
                $up,
                $down
            );
        }
    }

    public function squashMigration(): string
    {
        $database  = $this->setting->getConnection()->getDatabaseName();
        $path      = $this->filenameGenerator->makeCreatePath($database);
        $className = $this->filenameGenerator->makeCreateClassName($database);
        $this->migrationWriter->squashMigrations($path, $this->setting->getStubPath(), $className);
        return $path;
    }

    /**
     * @param  string  $path
     * @param  string  $className
     * @param  \KitLoong\MigrationsGenerator\Generators\Blueprint\SchemaBlueprint  $up
     * @param  \KitLoong\MigrationsGenerator\Generators\Blueprint\SchemaBlueprint  $down
     * @return string
     */
    private function writeMigration(string $path, string $className, SchemaBlueprint $up, SchemaBlueprint $down): string
    {
        $this->migrationWriter->writeTo(
            $path,
            $this->setting->getStubPath(),
            $className,
            $up,
            $down
        );

        return $path;
    }
}
