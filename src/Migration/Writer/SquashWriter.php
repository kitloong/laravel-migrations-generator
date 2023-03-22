<?php

namespace KitLoong\MigrationsGenerator\Migration\Writer;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use KitLoong\MigrationsGenerator\Migration\Blueprint\WritableBlueprint;
use KitLoong\MigrationsGenerator\Migration\Enum\Space;
use KitLoong\MigrationsGenerator\Support\MigrationNameHelper;

class SquashWriter
{
    private $migrationNameHelper;
    private $migrationStub;

    public function __construct(MigrationNameHelper $migrationNameHelper, MigrationStub $migrationStub)
    {
        $this->migrationNameHelper = $migrationNameHelper;
        $this->migrationStub       = $migrationStub;
    }

    /**
     * Writes migration `up` and `down` to two temporary path respectively.
     * Append new content into `up`.
     * Prepend new content into `down`.
     *
     * @param  \Illuminate\Support\Collection<\KitLoong\MigrationsGenerator\Migration\Blueprint\WritableBlueprint>  $upBlueprints
     * @param  \Illuminate\Support\Collection<\KitLoong\MigrationsGenerator\Migration\Blueprint\WritableBlueprint>  $downBlueprints
     */
    public function writeToTemp(Collection $upBlueprints, Collection $downBlueprints): void
    {
        $upTempPath  = $this->migrationNameHelper->makeUpTempPath();
        $prettySpace = $this->getSpaceIfFileExists($upTempPath);
        $upString    = $upBlueprints->map(function (WritableBlueprint $up) {
            return $up->toString();
        })->implode(Space::LINE_BREAK() . Space::TAB() . Space::TAB()); // Add tab to prettify
        File::append($upTempPath, $prettySpace . $upString);

        $downTempPath = $this->migrationNameHelper->makeDownTempPath();
        $prettySpace  = $this->getSpaceIfFileExists($downTempPath);
        $downString   = $downBlueprints->map(function (WritableBlueprint $down) {
            return $down->toString();
        })->implode(Space::LINE_BREAK() . Space::TAB() . Space::TAB()); // Add tab to prettify
        File::prepend($downTempPath, $downString . $prettySpace);
    }

    /**
     * Cleans all migration temporary paths.
     */
    public function cleanTemps(): void
    {
        File::delete($this->migrationNameHelper->makeUpTempPath());
        File::delete($this->migrationNameHelper->makeDownTempPath());
    }

    /**
     * Squash temporary paths into single migration file.
     *
     * @param  string  $path  Migration file destination path.
     * @param  string  $stubPath  Migration stub file path.
     */
    public function squashMigrations(string $path, string $stubPath, string $className): void
    {
        $use = implode(Space::LINE_BREAK(), [
            'use Illuminate\Database\Migrations\Migration;',
            'use Illuminate\Database\Schema\Blueprint;',
            'use Illuminate\Support\Facades\DB;',
            'use Illuminate\Support\Facades\Schema;',
        ]);

        $upTempPath   = $this->migrationNameHelper->makeUpTempPath();
        $downTempPath = $this->migrationNameHelper->makeDownTempPath();

        try {
            File::put(
                $path,
                $this->migrationStub->populateStub(
                    $this->migrationStub->getStub($stubPath),
                    $use,
                    $className,
                    File::get($upTempPath),
                    File::get($downTempPath)
                )
            );
        } catch (FileNotFoundException $e) {
            // Do nothing.
        } finally {
            File::delete($upTempPath);
            File::delete($downTempPath);
        }
    }

    private function getSpaceIfFileExists(string $path): string
    {
        if (!File::exists($path)) {
            return '';
        }

        return Space::LINE_BREAK() . Space::LINE_BREAK() . Space::TAB() . Space::TAB();
    }
}
