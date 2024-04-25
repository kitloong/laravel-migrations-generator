<?php

namespace KitLoong\MigrationsGenerator\Support;

use Illuminate\Support\Str;
use KitLoong\MigrationsGenerator\Setting;

class MigrationNameHelper
{
    use TableName;

    public function __construct(private readonly Setting $setting)
    {
    }

    /**
     * Makes file path for temporary `up` migration.
     */
    public function makeUpTempPath(): string
    {
        $path = $this->setting->getPath();
        return "$path/lmg-up-temp";
    }

    /**
     * Makes file path for temporary `down` migration.
     */
    public function makeDownTempPath(): string
    {
        $path = $this->setting->getPath();
        return "$path/lmg-down-temp";
    }

    /**
     * Makes migration filename by given naming pattern.
     *
     * @param  string  $pattern  Naming pattern for migration filename.
     * @param  string  $datetime  Current datetime for filename prefix.
     * @param  string  $name  Name.
     */
    public function makeFilename(string $pattern, string $datetime, string $name): string
    {
        $path     = $this->setting->getPath();
        $filename = $pattern;
        $replace  = [
            '[datetime]' => $datetime,
            '[name]'     => $this->removeSpecialCharacters($name),
        ];
        $filename = str_replace(array_keys($replace), $replace, $filename);
        return "$path/$filename";
    }

    /**
     * Makes migration class name by given naming pattern.
     *
     * @param  string  $pattern  Naming pattern for class.
     * @param  string  $name  Name.
     */
    public function makeClassName(string $pattern, string $name): string
    {
        $className = $pattern;
        $replace   = [
            '[datetime]_' => '',
            '[name]'      => $this->removeSpecialCharacters($name),
            '.php'        => '',
        ];
        return Str::studly(str_replace(array_keys($replace), $replace, $className));
    }

    /**
     * Remove special characters except `_`.
     *
     * @param  string  $table  Table name.
     * @return string Table name without prefix.
     */
    private function removeSpecialCharacters(string $table): string
    {
        return (string) preg_replace('/[^a-zA-Z0-9_]/', '_', $table);
    }
}
