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
     * Creates table migration.
     *
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
     * Writes table schema into temp files.
     *
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
     * Creates foreign key migration.
     *
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
     * Writes foreign key schema into temp files.
     *
     * @param  \Doctrine\DBAL\Schema\Table  $table
     * @param  \Doctrine\DBAL\Schema\ForeignKeyConstraint[]  $foreignKeys
     */
    public function writeForeignKeysToTemp(Table $table, array $foreignKeys): void
    {
        $up   = $this->foreignKeyMigration->up($table, $foreignKeys);
        $down = $this->foreignKeyMigration->down($table, $foreignKeys);

        $this->squashWriter->writeToTemp($up, $down);
    }

    /**
     * Cleans all migration temporary paths.
     * Execute at the beginning, if `--squash` options provided.
     */
    public function cleanTemps(): void
    {
        $this->squashWriter->cleanTemps();
    }

    /**
     * Squash temporary paths into single migration file.
     *
     * @return string Squashed migration file path.
     */
    public function squashMigrations(): string
    {
        $database  = $this->setting->getConnection()->getDatabaseName();
        $path      = $this->filenameGenerator->makeTablePath($database);
        $className = $this->filenameGenerator->makeTableClassName($database);
        $this->squashWriter->squashMigrations($path, $this->setting->getStubPath(), $className);
        return $path;
    }

    /**
     * Writes migration files.
     *
     * @param  string  $path  Migration file destination path.
     * @param  string  $className
     * @param  \MigrationsGenerator\Generators\Blueprint\SchemaBlueprint  $up  Blueprint of migration `up`.
     * @param  \MigrationsGenerator\Generators\Blueprint\SchemaBlueprint  $down  Blueprint of migration `down`.
     * @return string Generated migration file path.
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
