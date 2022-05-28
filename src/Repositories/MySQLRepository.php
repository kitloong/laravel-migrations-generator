<?php

namespace KitLoong\MigrationsGenerator\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Repositories\Entities\MySQL\ShowColumn;

class MySQLRepository extends Repository
{
    /**
     * Show column by table and column name.
     *
     * @param  string  $table  Table name.
     * @param  string  $column  Column name.
     * @return \KitLoong\MigrationsGenerator\Repositories\Entities\MySQL\ShowColumn|null
     */
    public function showColumn(string $table, string $column): ?ShowColumn
    {
        $column = DB::selectOne("SHOW COLUMNS FROM `$table` where Field = '$column'");
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
     * @return \Illuminate\Support\Collection<string>
     */
    public function getEnumPresetValues(string $table, string $column): Collection
    {
        $columns = DB::select("SHOW COLUMNS FROM `$table` where Field = '$column' AND Type LIKE 'enum(%'");
        if (count($columns) > 0) {
            $showColumn = new ShowColumn($columns[0]);
            $value      = substr(
                str_replace('enum(\'', '', $showColumn->getType()),
                0,
                -2
            );
            return new Collection(explode("','", $value));
        }
        return new Collection();
    }

    /**
     * Get set values.
     *
     * @param  string  $table  Table name.
     * @param  string  $column  Column name.
     * @return \Illuminate\Support\Collection<string>
     */
    public function getSetPresetValues(string $table, string $column): Collection
    {
        $columns = DB::select("SHOW COLUMNS FROM `$table` where Field = '$column' AND Type LIKE 'set(%'");
        if (count($columns) > 0) {
            $showColumn = new ShowColumn($columns[0]);
            $value      = substr(
                str_replace('set(\'', '', $showColumn->getType()),
                0,
                -2
            );
            return new Collection(explode("','", $value));
        }

        return new Collection();
    }

    /**
     * Checks if column has `on update CURRENT_TIMESTAMP`
     *
     * @param  string  $table  Table name.
     * @param  string  $column  Column name.
     * @return bool
     */
    public function isOnUpdateCurrentTimestamp(string $table, string $column): bool
    {
        // MySQL5.7 shows "on update CURRENT_TIMESTAMP"
        // MySQL8 shows "DEFAULT_GENERATED on update CURRENT_TIMESTAMP"
        $showColumn = DB::select(
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
