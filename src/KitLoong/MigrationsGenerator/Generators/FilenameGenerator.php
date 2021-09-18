<?php

namespace KitLoong\MigrationsGenerator\Generators;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use KitLoong\MigrationsGenerator\MigrationsGeneratorSetting;

class FilenameGenerator
{
    private $createDatePrefix;
    private $createPattern;
    private $foreignKeyDatePrefix;
    private $foreignKeyPattern;
    private $tableNameGenerator;

    public function __construct(TableNameGenerator $tableNameGenerator)
    {
        $this->tableNameGenerator = $tableNameGenerator;

        $this->createDatePrefix     = (string) date('Y_m_d_His');
        $this->foreignKeyDatePrefix = (string) date('Y_m_d_His', strtotime('+1 second'));

        $this->createPattern     = Config::get('generators.config.filename_pattern.create');
        $this->foreignKeyPattern = Config::get('generators.config.filename_pattern.foreign_key');
    }

    public function makeCreateClassName(string $table): string
    {
        $className = $this->makeClassName(
            $this->createPattern,
            $table
        );
        return Str::studly($className);
    }

    public function makeCreatePath(string $table): string
    {
        return $this->makeFilename(
            $this->createPattern,
            $this->createDatePrefix,
            $table
        );
    }

    public function makeForeignKeyClassName(string $table): string
    {
        $className = $this->makeClassName(
            $this->foreignKeyPattern,
            $table
        );
        return Str::studly($className);
    }

    public function makeForeignKeyPath(string $table): string
    {
        return $this->makeFilename(
            $this->foreignKeyPattern,
            $this->foreignKeyDatePrefix,
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
            '{{ datetime_prefix }}' => $datetimePrefix,
            '{{ table }}'           => $this->stripTablePrefix($table),
        ];
        $filename = str_replace(array_keys($replace), $replace, $filename);
        return "$path/$filename";
    }

    private function makeClassName(string $pattern, string $table): string
    {
        $className = $pattern;
        $replace   = [
            '{{ datetime_prefix }}_' => '',
            '{{ table }}'            => $this->stripTablePrefix($table),
            '.php'                   => '',
        ];
        return str_replace(array_keys($replace), $replace, $className);
    }

    private function stripTablePrefix(string $table): string
    {
        $tableNameEscaped = (string) preg_replace('/[^a-zA-Z0-9_]/', '_', $table);
        return $this->tableNameGenerator->stripPrefix($tableNameEscaped);
    }
}
