<?php

namespace MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Table;
use MigrationsGenerator\Generators\Blueprint\SchemaBlueprint;
use MigrationsGenerator\Generators\Writer\MigrationWriter;
use MigrationsGenerator\Generators\Writer\SquashWriter;
use MigrationsGenerator\MigrationsGeneratorSetting;

class Generator
{
    private $migrationWriter;
    private $squashWriter;
    private $filenameGenerator;
    private $foreignKeyMigration;
    private $tableMigration;
    private $setting;

    public function __construct(
        MigrationWriter $migrationWriter,
        SquashWriter $squashWriter,
        FilenameGenerator $filenameGenerator,
        ForeignKeyMigration $foreignKeyMigration,
        TableMigration $tableMigration,
        MigrationsGeneratorSetting $setting
    ) {
        $this->migrationWriter     = $migrationWriter;
        $this->squashWriter        = $squashWriter;
        $this->filenameGenerator   = $filenameGenerator;
        $this->foreignKeyMigration = $foreignKeyMigration;
        $this->tableMigration      = $tableMigration;
        $this->setting             = $setting;
    }

    /**
     * @param  \Doctrine\DBAL\Schema\Table  $table
     * @param  \Doctrine\DBAL\Schema\Column[]  $columns
     * @param  \Doctrine\DBAL\Schema\Index[]  $indexes
     * @return string Generated file path.
     */
    public function writeTableToMigrationFile(Table $table, array $columns, array $indexes): string
    {
        $up   = $this->tableMigration->up($table, $columns, $indexes);
        $down = $this->tableMigration->down($table);

        return $this->writeMigration(
            $this->filenameGenerator->makeTablePath($table->getName()),
            $this->filenameGenerator->makeTableClassName($table->getName()),
            $up,
            $down
        );
    }

    /**
     * @param  \Doctrine\DBAL\Schema\Table  $table
     * @param  \Doctrine\DBAL\Schema\Column[]  $columns
     * @param  \Doctrine\DBAL\Schema\Index[]  $indexes
     */
    public function writeTableToTemp(Table $table, array $columns, array $indexes): void
    {
        $up   = $this->tableMigration->up($table, $columns, $indexes);
        $down = $this->tableMigration->down($table);

        $this->squashWriter->writeToTemp($up, $down);
    }

    /**
     * @param  \Doctrine\DBAL\Schema\Table  $table
     * @param  \Doctrine\DBAL\Schema\ForeignKeyConstraint[]  $foreignKeys
     * @return string Generated file path.
     */
    public function writeForeignKeysToMigrationFile(Table $table, array $foreignKeys): string
    {
        $up   = $this->foreignKeyMigration->up($table, $foreignKeys);
        $down = $this->foreignKeyMigration->down($table, $foreignKeys);

        return $this->writeMigration(
            $this->filenameGenerator->makeForeignKeyPath($table->getName()),
            $this->filenameGenerator->makeForeignKeyClassName($table->getName()),
            $up,
            $down
        );
    }

    /**
     * @param  \Doctrine\DBAL\Schema\Table  $table
     * @param  \Doctrine\DBAL\Schema\ForeignKeyConstraint[]  $foreignKeys
     */
    public function writeForeignKeysToTemp(Table $table, array $foreignKeys): void
    {
        $up   = $this->foreignKeyMigration->up($table, $foreignKeys);
        $down = $this->foreignKeyMigration->down($table, $foreignKeys);

        $this->squashWriter->writeToTemp($up, $down);
    }

    public function cleanTemps(): void
    {
        $this->squashWriter->cleanTemps();
    }

    public function squashMigrations(): string
    {
        $database  = $this->setting->getConnection()->getDatabaseName();
        $path      = $this->filenameGenerator->makeTablePath($database);
        $className = $this->filenameGenerator->makeTableClassName($database);
        $this->squashWriter->squashMigrations($path, $this->setting->getStubPath(), $className);
        return $path;
    }

    /**
     * @param  string  $path
     * @param  string  $className
     * @param  \MigrationsGenerator\Generators\Blueprint\SchemaBlueprint  $up
     * @param  \MigrationsGenerator\Generators\Blueprint\SchemaBlueprint  $down
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
