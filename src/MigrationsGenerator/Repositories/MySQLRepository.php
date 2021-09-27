<?php

namespace MigrationsGenerator\Repositories;

use MigrationsGenerator\MigrationsGeneratorSetting;
use MigrationsGenerator\Schema\MySQL\ShowColumn;

class MySQLRepository extends Repository
{
    /**
     * Get database charset and collation.
     *
     * @return array{charset: string, collation: string}
     */
    public function getDatabaseCollation(): array
    {
        $setting = app(MigrationsGeneratorSetting::class);
        $columns = $setting->getConnection()->select("SELECT @@character_set_database, @@collation_database");
        return [
            'charset' => $columns[0]->{'@@character_set_database'}, 'collation' => $columns[0]->{'@@collation_database'}
        ];
    }

    /**
     * Show column by table and column name.
     *
     * @param  string  $table  Table name.
     * @param  string  $column  Column name.
     * @return \MigrationsGenerator\Schema\MySQL\ShowColumn|null
     */
    public function showColumn(string $table, string $column): ?ShowColumn
    {
        $setting = app(MigrationsGeneratorSetting::class);
        $column  = $setting->getConnection()
            ->selectOne("SHOW COLUMNS FROM `$table` where Field = '$column'");
        if ($column !== null) {
            return new ShowColumn($column);
        }
        return null;
    }

    /**
     * Get enum values.
     *
     * @param  string  $table  Table name.
     * @param  string  $column  Column name.
     * @return string[]
     */
    public function getEnumPresetValues(string $table, string $column): array
    {
        $setting = app(MigrationsGeneratorSetting::class);
        $columns = $setting->getConnection()->select("SHOW COLUMNS FROM `$table` where Field = '$column' AND Type LIKE 'enum(%'");
        if (count($columns) > 0) {
            $showColumn = new ShowColumn($columns[0]);
            $value      = substr(
                str_replace('enum(\'', '', $showColumn->getType()),
                0,
                -2
            );
            return explode("','", $value);
        }
        return [];
    }

    /**
     * Get set values.
     *
     * @param  string  $table  Table name.
     * @param  string  $column  Column name.
     * @return string[]
     */
    public function getSetPresetValues(string $table, string $column): array
    {
        $setting = app(MigrationsGeneratorSetting::class);
        $columns = $setting->getConnection()->select("SHOW COLUMNS FROM `$table` where Field = '$column' AND Type LIKE 'set(%'");
        if (count($columns) > 0) {
            $showColumn = new ShowColumn($columns[0]);
            $value      = substr(
                str_replace('set(\'', '', $showColumn->getType()),
                0,
                -2
            );
            return explode("','", $value);
        }

        return [];
    }

    /**
     * Checks if column has `on update CURRENT_TIMESTAMP`
     *
     * @param  string  $table  Table name.
     * @param  string  $column  Column name.
     * @return bool
     */
    public function useOnUpdateCurrentTimestamp(string $table, string $column): bool
    {
        $setting = app(MigrationsGeneratorSetting::class);

        // MySQL5.7 shows "on update CURRENT_TIMESTAMP"
        // MySQL8 shows "DEFAULT_GENERATED on update CURRENT_TIMESTAMP"
        $showColumn = $setting->getConnection()
            ->select(
                "SHOW COLUMNS FROM `$table`
                WHERE Field = '$column'
                    AND Type = 'timestamp'
                    AND EXTRA LIKE '%on update CURRENT_TIMESTAMP%'"
            );
        if (count($showColumn) > 0) {
            return true;
        }

        return false;
    }
}
