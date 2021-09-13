<?php

namespace KitLoong\MigrationsGenerator\Generators\Writer;

use Illuminate\Support\Facades\File;
use KitLoong\MigrationsGenerator\Generators\Blueprint\SchemaBlueprint;

class MigrationWriter
{
    public function writeTo(
        string $path,
        string $stubPath,
        string $className,
        SchemaBlueprint $up,
        SchemaBlueprint $down
    ): void {
        $stub = $this->getStub($stubPath);
        File::put(
            $path,
            $this->populateStub($stub, $className, $up, $down)
        );
    }

    /**
     * Get the migration stub file.
     *
     * @param  string  $stubPath
     * @return string
     */
    private function getStub(string $stubPath): string
    {
        return File::get($stubPath);
    }

    /**
     * Populate the place-holders in the migration stub.
     *
     * @param  string  $stub
     * @param  string  $className
     * @param  \KitLoong\MigrationsGenerator\Generators\Blueprint\SchemaBlueprint  $up
     * @param  \KitLoong\MigrationsGenerator\Generators\Blueprint\SchemaBlueprint  $down
     * @return string
     */
    private function populateStub(string $stub, string $className, SchemaBlueprint $up, SchemaBlueprint $down): string
    {
        $content = $stub;
        $replace = [
            '{{ class }}' => $className,
            '{{ up }}'    => $up->toString(),
            '{{ down }}'  => $down->toString(),
        ];
        return str_replace(array_keys($replace), $replace, $content);
    }
}
