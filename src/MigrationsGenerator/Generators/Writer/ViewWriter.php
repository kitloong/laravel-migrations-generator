<?php

namespace MigrationsGenerator\Generators\Writer;

use Illuminate\Support\Facades\File;
use MigrationsGenerator\Generators\Blueprint\WritableBlueprint;

class ViewWriter
{
    private $migrationStub;

    public function __construct(MigrationStub $migrationStub)
    {
        $this->migrationStub = $migrationStub;
    }

    /**
     * Writes migration to destination.
     *
     * @param  string  $path  Migration file destination path.
     * @param  string  $stubPath  Migration stub file path.
     * @param  string  $className
     * @param  \MigrationsGenerator\Generators\Blueprint\WritableBlueprint  $up  Blueprint of migration `up`.
     * @param  \MigrationsGenerator\Generators\Blueprint\WritableBlueprint  $down  Blueprint of migration `down`.
     */
    public function writeTo(
        string $path,
        string $stubPath,
        string $className,
        WritableBlueprint $up,
        WritableBlueprint $down
    ): void {
        $stub = $this->migrationStub->getStub($stubPath);
        $use = implode(WriterConstant::LINE_BREAK, [
            'use Illuminate\Database\Migrations\Migration;',
            'use Illuminate\Support\Facades\DB;',
        ]);
        File::put(
            $path,
            $this->migrationStub->populateStub($stub, $use, $className, $up->toString(), $down->toString())
        );
    }
}
