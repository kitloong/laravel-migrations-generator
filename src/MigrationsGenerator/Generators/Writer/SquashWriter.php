<?php

namespace MigrationsGenerator\Generators\Writer;

use Illuminate\Support\Facades\File;
use MigrationsGenerator\Generators\Blueprint\SchemaBlueprint;
use MigrationsGenerator\Generators\FilenameGenerator;

class SquashWriter
{
    private $filenameGenerator;
    private $migrationStub;

    public function __construct(FilenameGenerator $filenameGenerator, MigrationStub $migrationStub)
    {
        $this->filenameGenerator = $filenameGenerator;
        $this->migrationStub     = $migrationStub;
    }

    public function writeToTemp(SchemaBlueprint $upBlueprint, SchemaBlueprint $downBlueprint): void
    {
        if (File::missing($upTempPath = $this->filenameGenerator->makeUpTempPath())) {
            $prettySpace = '';
        } else {
            $prettySpace = PHP_EOL.PHP_EOL.WriterConstant::TAB.WriterConstant::TAB;
        }
        File::append($upTempPath, $prettySpace.$upBlueprint->toString());

        if (File::missing($downTempPath = $this->filenameGenerator->makeDownTempPath())) {
            $prettySpace = '';
        } else {
            $prettySpace = PHP_EOL.PHP_EOL.WriterConstant::TAB.WriterConstant::TAB;
        }
        File::prepend($downTempPath, $downBlueprint->toString().$prettySpace);
    }

    public function cleanTemps(): void
    {
        File::delete($this->filenameGenerator->makeUpTempPath());
        File::delete($this->filenameGenerator->makeDownTempPath());
    }

    public function squashMigrations(string $path, string $stubPath, string $className): void
    {
        File::put(
            $path,
            $this->migrationStub->populateStub(
                $this->migrationStub->getStub($stubPath),
                $className,
                File::get($upTempPath = $this->filenameGenerator->makeUpTempPath()),
                File::get($downTempPath = $this->filenameGenerator->makeDownTempPath())
            )
        );

        File::delete($upTempPath);
        File::delete($downTempPath);
    }
}
