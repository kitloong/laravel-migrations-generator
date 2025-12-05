<?php

namespace KitLoong\MigrationsGenerator\Repositories;

use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use KitLoong\MigrationsGenerator\Repositories\Entities\MySQL\ShowColumn;
use KitLoong\MigrationsGenerator\Repositories\Entities\ProcedureDefinition;

class MySQLRepository extends Repository
{
    /**
     * Show column by table and column name.
     *
     * @param  string  $table  Table name.
     * @param  string  $column  Column name.
     */
    public function showColumn(string $table, string $column): ?ShowColumn
    {
        $result = DB::selectOne("SHOW COLUMNS FROM `$table` WHERE Field = '$column'");
        return $result === null ? null : new ShowColumn($result);
    }

    /**
     * Checks if column has `on update CURRENT_TIMESTAMP`
     *
     * @param  string  $table  Table name.
     * @param  string  $column  Column name.
     */
    public function isOnUpdateCurrentTimestamp(string $table, string $column): bool
    {
        // MySQL5.7 shows "on update CURRENT_TIMESTAMP"
        // MySQL8 shows "DEFAULT_GENERATED on update CURRENT_TIMESTAMP"
        $result = DB::selectOne(
            "SHOW COLUMNS FROM `$table`
                WHERE Field = '$column'
                    AND (Type LIKE 'timestamp%' OR Type LIKE 'datetime%')
                    AND Extra LIKE '%on update CURRENT_TIMESTAMP%'",
        );
        return !($result === null);
    }

    /**
     * Get a list of stored procedures.
     *
     * @param  string  $type  'PROCEDURE' or 'FUNCTION'.
     * @return \Illuminate\Support\Collection<int, \KitLoong\MigrationsGenerator\Repositories\Entities\ProcedureDefinition>
     */
    public function getProcedures(): Collection {
        $list       = new Collection();
        $procedures = DB::select("SHOW PROCEDURE STATUS WHERE Db='" . DB::getDatabaseName() . "'");
        $functions = DB::select("SHOW FUNCTION STATUS WHERE Db='" . DB::getDatabaseName() . "'");

        $procedures = array_merge($procedures, $functions);

        foreach ($procedures as $procedure) {
            // Change all keys to lowercase.
            $procedureArr = array_change_key_case((array) $procedure);
            $type = strtoupper($procedureArr['type']);
            $createProc   = $this->getProcedure($procedureArr['name'], $type);

            // Change all keys to lowercase.
            $createProcArr = array_change_key_case((array) $createProc);

            $definitionKey = $type === 'PROCEDURE' ? 'create procedure' : 'create function';

            if ($createProcArr[$definitionKey] === null || $createProcArr[$definitionKey] === '') {
                continue;
            }

            // Remove DEFINER from procedure definition.
            $definition = preg_replace("/(?=DEFINER=)(.+?)(?= $type) /u", '', $createProcArr[$definitionKey]);

            $list->push(new ProcedureDefinition($procedureArr['name'], $definition));
        }

        return $list;
    }

    /**
     * Get the SRID by table and column name.
     */
    public function getSrID(string $table, string $column): ?int
    {
        try {
            $srsID = DB::selectOne(
                "SELECT SRS_ID
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = '" . DB::getDatabaseName() . "'
                    AND TABLE_NAME = '" . $table . "'
                    AND COLUMN_NAME = '" . $column . "'",
            );
        } catch (QueryException $exception) {
            if (
                // `SRS_ID` available since MySQL 8.0.3.
                // https://dev.mysql.com/doc/relnotes/mysql/8.0/en/news-8-0-3.html
                Str::contains(
                    $exception->getMessage(),
                    "SQLSTATE[42S22]: Column not found: 1054 Unknown column 'SRS_ID'",
                    true,
                )
            ) {
                return null;
            }

            throw $exception;
        }

        if ($srsID === null) {
            return null;
        }

        $srsIDArr = array_change_key_case((array) $srsID);
        return $srsIDArr['srs_id'] ?? null;
    }

    /**
     * Get single stored procedure by name.
     *
     * @param  string  $procedure  Procedure name.
     * @return mixed
     */
    private function getProcedure(string $procedure, $type = 'PROCEDURE') {
        $type = strtoupper($type);
        $type = in_array($type, ['PROCEDURE', 'FUNCTION']) ? $type : 'PROCEDURE';
        return DB::selectOne("SHOW CREATE $type $procedure");
    }
}
