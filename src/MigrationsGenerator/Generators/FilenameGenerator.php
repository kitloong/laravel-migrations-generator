<?php

namespace MigrationsGenerator\Generators;

use Illuminate\Support\Str;
use MigrationsGenerator\MigrationsGeneratorSetting;

class FilenameGenerator
{
    private $tableNameGenerator;

    public function __construct(TableNameGenerator $tableNameGenerator)
    {
        $this->tableNameGenerator = $tableNameGenerator;
    }

    public function makeTableClassName(string $table): string
    {
        $className = $this->makeClassName(
            app(MigrationsGeneratorSetting::class)->getTableFilename(),
            $table
        );
        return Str::studly($className);
    }

    public function makeTablePath(string $table): string
    {
        return $this->makeFilename(
            app(MigrationsGeneratorSetting::class)->getTableFilename(),
            (string) date('Y_m_d_His'),
            $table
        );
    }

    public function makeForeignKeyClassName(string $table): string
    {
        $className = $this->makeClassName(
            app(MigrationsGeneratorSetting::class)->getFkFilename(),
            $table
        );
        return Str::studly($className);
    }

    public function makeForeignKeyPath(string $table): string
    {
        return $this->makeFilename(
            app(MigrationsGeneratorSetting::class)->getFkFilename(),
            (string) date('Y_m_d_His', strtotime('+1 second')),
            $table
        );
    }

    public function makeUpTempPath(): string
    {
        $path = app(MigrationsGeneratorSetting::class)->getPath();
        return "$path/lmg-up-temp";
    }

    public function makeDownTempPath(): string
    {
        $path = app(MigrationsGeneratorSetting::class)->getPath();
        return "$path/lmg-down-temp";
    }

    private function makeFilename(string $pattern, string $datetimePrefix, string $table): string
    {
        $path     = app(MigrationsGeneratorSetting::class)->getPath();
        $filename = $pattern;
        $replace  = [
            '[datetime_prefix]' => $datetimePrefix,
            '[table]'           => $this->stripTablePrefix($table),
        ];
        $filename = str_replace(array_keys($replace), $replace, $filename);
        return "$path/$filename";
    }

    private function makeClassName(string $pattern, string $table): string
    {
        $className = $pattern;
        $replace   = [
            '[datetime_prefix]_' => '',
            '[table]'            => $this->stripTablePrefix($table),
            '.php'               => '',
        ];
        return str_replace(array_keys($replace), $replace, $className);
    }

    private function stripTablePrefix(string $table): string
    {
        $tableNameEscaped = (string) preg_replace('/[^a-zA-Z0-9_]/', '_', $table);
        return $this->tableNameGenerator->stripPrefix($tableNameEscaped);
    }
}
