<?php

namespace KitLoong\MigrationsGenerator\Support;

use Carbon\Carbon;
use Illuminate\Support\Str;
use KitLoong\MigrationsGenerator\Setting;

class FilenameHelper
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
            $this->removeSpecialCharacters($withoutPrefix)
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
            $this->removeSpecialCharacters($withoutPrefix)
        );
    }

    /**
     * Makes class name for view migration.
     *
     * @param  string  $view  View name.
     * @return string
     */
    public function makeViewClassName(string $view): string
    {
        $withoutPrefix = $this->stripTablePrefix($view);
        return $this->makeClassName(
            $this->setting->getViewFilename(),
            $this->removeSpecialCharacters($withoutPrefix)
        );
    }

    /**
     * Makes file path for view migration.
     *
     * @param  string  $view  View name.
     * @return string
     */
    public function makeViewPath(string $view): string
    {
        $withoutPrefix = $this->stripTablePrefix($view);
        return $this->makeFilename(
            $this->setting->getViewFilename(),
            Carbon::parse($this->setting->getDate())->addSecond()->format('Y_m_d_His'),
            $this->removeSpecialCharacters($withoutPrefix)
        );
    }

    /**
     * Makes class name for stored procedure migration.
     *
     * @param  string  $procedure  Stored procedure name.
     * @return string
     */
    public function makeProcedureClassName(string $procedure): string
    {
        return $this->makeClassName(
            $this->setting->getProcedureFilename(),
            $this->removeSpecialCharacters($procedure)
        );
    }

    /**
     * Makes file path for stored procedure migration.
     *
     * @param  string  $procedure  Stored procedure name.
     * @return string
     */
    public function makeProcedurePath(string $procedure): string
    {
        return $this->makeFilename(
            $this->setting->getProcedureFilename(),
            Carbon::parse($this->setting->getDate())->addSecond()->format('Y_m_d_His'),
            $this->removeSpecialCharacters($procedure)
        );
    }

    /**
     * Makes class name for foreign key migration.
     *
     * @param  string  $table  Table name.
     * @return string
     */
    public function makeForeignKeyClassName(string $table): string
    {
        $withoutPrefix = $this->stripTablePrefix($table);
        return $this->makeClassName(
            $this->setting->getFkFilename(),
            $this->removeSpecialCharacters($withoutPrefix)
        );
    }

    /**
     * Makes file path for foreign key migration.
     *
     * @param  string  $table
     * @return string
     */
    public function makeForeignKeyPath(string $table): string
    {
        $withoutPrefix = $this->stripTablePrefix($table);
        return $this->makeFilename(
            $this->setting->getFkFilename(),
            Carbon::parse($this->setting->getDate())->addSecond()->format('Y_m_d_His'),
            $this->removeSpecialCharacters($withoutPrefix)
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
     * @param  string  $datetimePrefix  Current datetime for filename prefix.
     * @param  string  $name  Name.
     * @return string
     */
    private function makeFilename(string $pattern, string $datetimePrefix, string $name): string
    {
        $path     = $this->setting->getPath();
        $filename = $pattern;
        $replace  = [
            '[datetime]' => $datetimePrefix,
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
    private function makeClassName(string $pattern, string $name): string
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
