<?php

namespace KitLoong\MigrationsGenerator\Migration;

use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Migration\Writer\SquashWriter;
use KitLoong\MigrationsGenerator\Setting;
use KitLoong\MigrationsGenerator\Support\FilenameHelper;

class Squash
{
    private $squashWriter;
    private $filenameHelper;
    private $setting;

    public function __construct(
        SquashWriter $squashWriter,
        FilenameHelper $filenameHelper,
        Setting $setting
    ) {
        $this->squashWriter   = $squashWriter;
        $this->filenameHelper = $filenameHelper;
        $this->setting        = $setting;
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
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function squashMigrations(): string
    {
        $database  = DB::getDatabaseName();
        $path      = $this->filenameHelper->makeTablePath($database);
        $className = $this->filenameHelper->makeTableClassName($database);
        $this->squashWriter->squashMigrations($path, $this->setting->getStubPath(), $className);
        return $path;
    }
}
