<?php

namespace MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Table;
use MigrationsGenerator\Generators\Writer\MigrationWriter;
use MigrationsGenerator\Generators\Writer\SquashWriter;
use MigrationsGenerator\Generators\Writer\ViewWriter;
use MigrationsGenerator\MigrationsGeneratorSetting;
use MigrationsGenerator\Models\View;

class Generator
{
    private $migrationWriter;
    private $viewWriter;
    private $squashWriter;
    private $filenameGenerator;
    private $foreignKeyMigration;
    private $tableMigration;
    private $viewMigration;
    private $setting;

    public function __construct(
        MigrationWriter $migrationWriter,
        ViewWriter $viewWriter,
        SquashWriter $squashWriter,
        FilenameGenerator $filenameGenerator,
        ForeignKeyMigration $foreignKeyMigration,
        TableMigration $tableMigration,
        ViewMigration $viewMigration,
        MigrationsGeneratorSetting $setting
    ) {
        $this->migrationWriter     = $migrationWriter;
        $this->viewWriter          = $viewWriter;
        $this->squashWriter        = $squashWriter;
        $this->filenameGenerator   = $filenameGenerator;
        $this->foreignKeyMigration = $foreignKeyMigration;
        $this->tableMigration      = $tableMigration;
        $this->viewMigration       = $viewMigration;
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

        $this->migrationWriter->writeTo(
            $path = $this->filenameGenerator->makeTablePath($table->getName()),
            $this->setting->getStubPath(),
            $this->filenameGenerator->makeTableClassName($table->getName()),
            $up,
            $down
        );

        return $path;
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
     * Creates view migration.
     *
     * @param  \MigrationsGenerator\Models\View  $view
     * @return string Generated file path.
     */
    public function writeViewToMigrationFile(View $view): string
    {
        $up   = $this->viewMigration->up($view);
        $down = $this->viewMigration->down($view);

        $this->viewWriter->writeTo(
            $path = $this->filenameGenerator->makeViewPath($view->getName()),
            $this->setting->getStubPath(),
            $this->filenameGenerator->makeViewClassName($view->getName()),
            $up,
            $down
        );

        return $path;
    }

    /**
     * Writes view into temp files.
     *
     * @param  \MigrationsGenerator\Models\View  $view
     */
    public function writeViewToTemp(View $view)
    {
        $up   = $this->viewMigration->up($view);
        $down = $this->viewMigration->down($view);

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

        $this->migrationWriter->writeTo(
            $path = $this->filenameGenerator->makeForeignKeyPath($table->getName()),
            $this->setting->getStubPath(),
            $this->filenameGenerator->makeForeignKeyClassName($table->getName()),
            $up,
            $down
        );

        return $path;
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
}
