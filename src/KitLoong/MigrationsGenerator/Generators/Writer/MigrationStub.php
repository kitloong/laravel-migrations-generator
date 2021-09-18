<?php

namespace KitLoong\MigrationsGenerator\Generators\Writer;

use Illuminate\Support\Facades\File;

class MigrationStub
{
    /**
     * Get the migration stub file.
     *
     * @param  string  $stubPath
     * @return string
     */
    public function getStub(string $stubPath): string
    {
        return File::get($stubPath);
    }

    /**
     * Populate the place-holders in the migration stub.
     *
     * @param  string  $stub
     * @param  string  $className
     * @param  string  $upContent
     * @param  string  $downContent
     * @return string
     */
    public function populateStub(string $stub, string $className, string $upContent, string $downContent): string
    {
        $content = $stub;
        $replace = [
            '{{ class }}' => $className,
            '{{ up }}'    => $upContent,
            '{{ down }}'  => $downContent,
        ];
        return str_replace(array_keys($replace), $replace, $content);
    }
}
