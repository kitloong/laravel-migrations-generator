<?php

namespace KitLoong\MigrationsGenerator\Migration;

use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Migration\Writer\SquashWriter;
use KitLoong\MigrationsGenerator\Setting;
use KitLoong\MigrationsGenerator\Support\MigrationNameHelper;

class Squash
{
    private $squashWriter;
    private $migrationNameHelper;
    private $setting;

    public function __construct(SquashWriter $squashWriter, MigrationNameHelper $migrationNameHelper, Setting $setting)
    {
        $this->squashWriter        = $squashWriter;
        $this->migrationNameHelper = $migrationNameHelper;
        $this->setting             = $setting;
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
            $this->setting->getDate()->format('Y_m_d_His'),
            DB::getDatabaseName()
        );

        $className = $this->migrationNameHelper->makeClassName(
            $this->setting->getTableFilename(),
            DB::getDatabaseName()
        );
        $this->squashWriter->squashMigrations($path, $this->setting->getStubPath(), $className);
        return $path;
    }
}
