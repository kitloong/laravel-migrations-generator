<?php

namespace KitLoong\MigrationsGenerator\Support;

use Illuminate\Support\Str;
use KitLoong\MigrationsGenerator\Setting;

class MigrationNameHelper
{
    use TableName;

    private $setting;

    public function __construct(Setting $setting)
    {
        $this->setting = $setting;
    }

    /**
     * Makes class name for table migration.
     *
     * @param  string  $table  Table name.
     * @return string
     */
    public function makeTableClassName(string $table): string
    {
        $withoutPrefix = $this->stripTablePrefix($table);
        return $this->makeClassName(
            $this->setting->getTableFilename(),
            $withoutPrefix
        );
    }

    /**
     * Makes file path for table migration.
     *
     * @param  string  $table  Table name.
     * @return string
     */
    public function makeTablePath(string $table): string
    {
        $withoutPrefix = $this->stripTablePrefix($table);
        return $this->makeFilename(
            $this->setting->getTableFilename(),
            $this->setting->getDate()->format('Y_m_d_His'),
            $withoutPrefix
        );
    }

    /**
     * Makes file path for temporary `up` migration.
     *
     * @return string
     */
    public function makeUpTempPath(): string
    {
        $path = $this->setting->getPath();
        return "$path/lmg-up-temp";
    }

    /**
     * Makes file path for temporary `down` migration.
     *
     * @return string
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
     * @return string
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
     * @return string
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
