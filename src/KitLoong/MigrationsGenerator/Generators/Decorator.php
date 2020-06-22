<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/29
 * Time: 11:59
 */

namespace KitLoong\MigrationsGenerator\Generators;

use KitLoong\MigrationsGenerator\MigrationGeneratorSetting;

class Decorator
{
    /**
     * Escape content with ' and wrap content with '
     *
     * @param  string  $args
     * @param  string  $quotes
     * @return string
     */
    public function columnDefaultToString(string $args, string $quotes = '\''): string
    {
        $args = addslashes($args);
        // To replace from ' to \\\'
        $args = str_replace($quotes, '\\\\\\\\'.$quotes, $args);

        return $quotes.$args.$quotes;
    }

    /**
     * Get Decorator
     * @param  string  $function
     * @param  array  $args
     * @return string
     */
    public function decorate(string $function, array $args): string
    {
        if (!empty($args)) {
            return $function.'('.implode(', ', $args).')';
        } else {
            return $function;
        }
    }

    public function addSlash(string $string): string
    {
        return addcslashes($string, "\\'");
    }

    public function tableWithoutPrefix(string $table): string
    {
        /** @var MigrationGeneratorSetting $setting */
        $setting = app(MigrationGeneratorSetting::class);

        return substr($table, strlen($setting->getConnection()->getTablePrefix()));
    }

    public function tableUsedInFilename(string $table): string
    {
        $tableNameEscaped = preg_replace('/[^a-zA-Z0-9_]/', '_', $table);
        return $this->tableWithoutPrefix($tableNameEscaped);
    }
}
