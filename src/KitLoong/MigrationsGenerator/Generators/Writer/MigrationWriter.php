<?php

namespace KitLoong\MigrationsGenerator\Generators\Writer;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use KitLoong\MigrationsGenerator\Generators\Blueprint\SchemaBlueprint;
use KitLoong\MigrationsGenerator\Support\Str;

class MigrationWriter
{
    private $str;

    /** @var string */
    private $className;

    public function __construct(Str $str)
    {
        $this->str = $str;
    }

    public function writeTo(
        string $path,
        SchemaBlueprint $up,
        SchemaBlueprint $down,
        ?string $userDefinedStubPath
    ): void {
        $stub = $this->getStub($userDefinedStubPath);
        File::put(
            $path,
            $this->populateStub($stub, $up, $down)
        );
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function setClassName(string $className): void
    {
        $this->className = $className;
    }

    /**
     * Get the migration stub file.
     *
     * @param  string|null  $userDefinedStubPath
     * @return string
     */
    private function getStub(?string $userDefinedStubPath): string
    {
        if ($userDefinedStubPath !== null) {
            // Use user defined stub
            return File::get($userDefinedStubPath);
        } else {
            $customStubPath = base_path('stubs/migration.stub');
            // Use framework stub file if exists.
            if (File::exists($customStubPath)) {
                return File::get($customStubPath);
            } else {
                // Use default stub
                return File::get(Config::get('generators.config.migration_template_path'));
            }
        }
    }

    /**
     * Populate the place-holders in the migration stub.
     *
     * @param  string  $stub
     * @param  \KitLoong\MigrationsGenerator\Generators\Blueprint\SchemaBlueprint  $up
     * @param  \KitLoong\MigrationsGenerator\Generators\Blueprint\SchemaBlueprint  $down
     * @return string
     */
    private function populateStub(string $stub, SchemaBlueprint $up, SchemaBlueprint $down): string
    {
        $content = $stub;
        $content = $this->str->replacePlaceholder('{{ class }}', $this->className, $content);
        $content = $this->str->replacePlaceholder('{{ up }}', $up->toString(), $content);
        return $this->str->replacePlaceholder('{{ down }}', $down->toString(), $content);
    }
}
