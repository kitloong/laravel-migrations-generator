<?php

namespace KitLoong\MigrationsGenerator\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Repositories\Entities\MySQL\ShowColumn;
use KitLoong\MigrationsGenerator\Repositories\Entities\ProcedureDefinition;

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
        $result = DB::selectOne("SHOW COLUMNS FROM `$table` where Field = '$column'");
        return $result === null ? null : new ShowColumn($result);
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
        $result = DB::selectOne("SHOW COLUMNS FROM `$table` where Field = '$column' AND Type LIKE 'enum(%'");

        if ($result === null) {
            return new Collection();
        }

        $showColumn = new ShowColumn($result);
        $value      = substr(
            str_replace('enum(\'', '', $showColumn->getType()),
            0,
            -2
        );
        return new Collection(explode("','", $value));
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
        $result = DB::selectOne("SHOW COLUMNS FROM `$table` where Field = '$column' AND Type LIKE 'set(%'");

        if ($result === null) {
            return new Collection();
        }

        $showColumn = new ShowColumn($result);
        $value      = substr(
            str_replace('set(\'', '', $showColumn->getType()),
            0,
            -2
        );
        return new Collection(explode("','", $value));
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
        $result = DB::selectOne(
            "SHOW COLUMNS FROM `$table`
                WHERE Field = '$column'
                    AND Type = 'timestamp'
                    AND EXTRA LIKE '%on update CURRENT_TIMESTAMP%'"
        );
        return !($result === null);
    }

    /**
     * Get a list of stored procedures.
     *
     * @return \Illuminate\Support\Collection<\KitLoong\MigrationsGenerator\Repositories\Entities\ProcedureDefinition>
     */
    public function getProcedures(): Collection
    {
        $list       = new Collection();
        $procedures = DB::select("SHOW PROCEDURE STATUS where DB='" . DB::getDatabaseName() . "'");

        foreach ($procedures as $procedure) {
            // Change all keys to lowercase.
            $procedureArr = array_change_key_case((array) $procedure);
            $createProc   = $this->getProcedure($procedureArr['name']);

            // Change all keys to lowercase.
            $createProcArr = array_change_key_case((array) $createProc);
            $list->push(new ProcedureDefinition($procedureArr['name'], $createProcArr['create procedure']));
        }

        return $list;
    }

    /**
     * Get single stored procedure by name.
     *
     * @param  string  $procedure  Procedure name.
     * @return mixed
     */
    private function getProcedure(string $procedure)
    {
        return DB::selectOne("SHOW CREATE PROCEDURE $procedure");
    }
}
