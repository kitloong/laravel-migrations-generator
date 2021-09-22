<?php

namespace MigrationsGenerator\Generators\Writer;

use Illuminate\Support\Facades\File;
use MigrationsGenerator\Generators\Blueprint\SchemaBlueprint;

class MigrationWriter
{
    private $migrationStub;

    public function __construct(MigrationStub $migrationStub)
    {
        $this->migrationStub     = $migrationStub;
    }

    public function writeTo(
        string $path,
        string $stubPath,
        string $className,
        SchemaBlueprint $up,
        SchemaBlueprint $down
    ): void {
        $stub = $this->migrationStub->getStub($stubPath);
        File::put(
            $path,
            $this->migrationStub->populateStub($stub, $className, $up->toString(), $down->toString())
        );
    }
}
