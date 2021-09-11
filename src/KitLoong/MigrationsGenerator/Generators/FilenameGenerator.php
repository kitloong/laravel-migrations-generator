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

    public function __construct()
    {
        $this->createDatePrefix     = (string) date('Y_m_d_His');
        $this->foreignKeyDatePrefix = (string) date('Y_m_d_His', strtotime('+1 second'));

        $this->createPattern     = Config::get('generators.config.filename_pattern.create');
        $this->foreignKeyPattern = Config::get('generators.config.filename_pattern.foreign_key');
    }

    public function generateCreateClassName(string $table): string
    {
        $className = $this->generateClassName(
            $this->createPattern,
            $table
        );
        return Str::studly($className);
    }

    public function generateCreatePath(string $table): string
    {
        return $this->generateFilename(
            $this->createPattern,
            $this->createDatePrefix,
            $table
        );
    }

    public function generateForeignKeyClassName(string $table): string
    {
        $className = $this->generateClassName(
            $this->foreignKeyPattern,
            $table
        );
        return Str::studly($className);
    }

    public function generateForeignKeyPath(string $table): string
    {
        return $this->generateFilename(
            $this->foreignKeyPattern,
            $this->foreignKeyDatePrefix,
            $table
        );
    }

    private function generateFilename(string $pattern, string $datetimePrefix, string $table): string
    {
        $path = app(MigrationsGeneratorSetting::class)->getPath();
        $filename = $pattern;
        $replace  = [
            '{{ datetime_prefix }}' => $datetimePrefix,
            '{{ table }}' => $this->stripTablePrefix($table),
        ];
        $filename = str_replace(array_keys($replace), $replace, $filename);
        return "$path/$filename";
    }

    private function generateClassName(string $pattern, string $table): string
    {
        $className = $pattern;
        $replace   = [
            '{{ datetime_prefix }}_' => '',
            '{{ table }}' => $this->stripTablePrefix($table),
            '.php' => '',
        ];
        return str_replace(array_keys($replace), $replace, $className);
    }

    private function stripTablePrefix(string $table): string
    {
        $setting          = app(MigrationsGeneratorSetting::class);
        $tableNameEscaped = (string) preg_replace('/[^a-zA-Z0-9_]/', '_', $table);
        return substr($tableNameEscaped, strlen($setting->getConnection()->getTablePrefix()));
    }
}
