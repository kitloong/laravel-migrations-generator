<?php

namespace KitLoong\MigrationsGenerator\Migration;

use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Migration\Writer\SquashWriter;
use KitLoong\MigrationsGenerator\Setting;
use KitLoong\MigrationsGenerator\Support\MigrationNameHelper;

class Squash
{
    public function __construct(
        private readonly SquashWriter $squashWriter,
        private readonly MigrationNameHelper $migrationNameHelper,
        private readonly Setting $setting,
    ) {
    }

    /**
     * Clean all migration temporary paths.
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
        $path = $this->migrationNameHelper->makeFilename(
            $this->setting->getTableFilename(),
            $this->setting->getDateForMigrationFilename(),
            DB::getDatabaseName(),
        );

        $className = $this->migrationNameHelper->makeClassName(
            $this->setting->getTableFilename(),
            DB::getDatabaseName(),
        );
        $this->squashWriter->squashMigrations($path, $this->setting->getStubPath(), $className);
        return $path;
    }
}
