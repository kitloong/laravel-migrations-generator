<?php

namespace KitLoong\MigrationsGenerator\Migration;

use Illuminate\Support\Collection;
use KitLoong\MigrationsGenerator\Schema\Models\Table;
use KitLoong\MigrationsGenerator\Schema\Models\View;

interface MigrationInterface
{
    /**
     * Create table migration.
     *
     * @param  \KitLoong\MigrationsGenerator\Schema\Models\Table  $table
     * @return string Generated file path.
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function writeTable(Table $table): string;

    /**
     * Write table schema into temp files.
     *
     * @param  \KitLoong\MigrationsGenerator\Schema\Models\Table  $table
     */
    public function writeTableToTemp(Table $table): void;

    /**
     * Create view migration.
     *
     * @param  \KitLoong\MigrationsGenerator\Schema\Models\View  $view
     * @return string Generated file path.
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function writeView(View $view): string;

    /**
     * Write view into temp files.
     *
     * @param  \KitLoong\MigrationsGenerator\Schema\Models\View  $view
     */
    public function writeViewToTemp(View $view): void;

    /**
     * Create foreign key migration.
     *
     * @param  string  $table
     * @param  \Illuminate\Support\Collection<\KitLoong\MigrationsGenerator\Schema\Models\ForeignKey>  $foreignKeys
     * @return string Generated file path.
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function writeTableForeignKeys(string $table, Collection $foreignKeys): string;

    /**
     * Write foreign key schema into temp files.
     *
     * @param  string  $table
     * @param  \Illuminate\Support\Collection<\KitLoong\MigrationsGenerator\Schema\Models\ForeignKey>  $foreignKeys
     */
    public function writeForeignKeysToTemp(string $table, Collection $foreignKeys): void;

    /**
     * Clean all migration temporary paths.
     * Execute at the beginning, if `--squash` options provided.
     */
    public function cleanTemps(): void;

    /**
     * Squash temporary paths into single migration file.
     *
     * @return string Squashed migration file path.
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function squashMigrations(): string;
}
