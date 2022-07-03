<?php

namespace KitLoong\MigrationsGenerator\Migration\Writer;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use KitLoong\MigrationsGenerator\Migration\Blueprint\WritableBlueprint;
use KitLoong\MigrationsGenerator\Migration\Enum\MigrationFileType;
use KitLoong\MigrationsGenerator\Migration\Enum\Space;

class MigrationWriter
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
     * @param  \KitLoong\MigrationsGenerator\Migration\Blueprint\WritableBlueprint  $up  Blueprint of migration `up`.
     * @param  \KitLoong\MigrationsGenerator\Migration\Blueprint\WritableBlueprint  $down  Blueprint of migration `down`.
     * @param  \KitLoong\MigrationsGenerator\Migration\Enum\MigrationFileType  $migrationFileType
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function writeTo(
        string $path,
        string $stubPath,
        string $className,
        WritableBlueprint $up,
        WritableBlueprint $down,
        MigrationFileType $migrationFileType
    ): void {
        $stub = $this->migrationStub->getStub($stubPath);

        $useDBFacade = false;
        $upString    = $up->toString();
        if (Str::contains($upString, 'DB::')) {
            $useDBFacade = true;
        }

        $use = implode(Space::LINE_BREAK(), $this->getImports($migrationFileType, $useDBFacade));

        File::put(
            $path,
            $this->migrationStub->populateStub($stub, $use, $className, $upString, $down->toString())
        );
    }

    /**
     * @param  \KitLoong\MigrationsGenerator\Migration\Enum\MigrationFileType  $migrationFileType
     * @param  bool  $useDBFacade
     * @return string[]
     */
    private function getImports(MigrationFileType $migrationFileType, bool $useDBFacade): array
    {
        if ($migrationFileType->equals(MigrationFileType::VIEW())) {
            return [
                'use Illuminate\Database\Migrations\Migration;',
                'use Illuminate\Support\Facades\DB;',
            ];
        }

        $imports = [
            'use Illuminate\Database\Migrations\Migration;',
            'use Illuminate\Database\Schema\Blueprint;',
            'use Illuminate\Support\Facades\Schema;',
        ];

        if ($useDBFacade) {
            $imports[] = 'use Illuminate\Support\Facades\DB;';
        }

        return $imports;
    }
}
