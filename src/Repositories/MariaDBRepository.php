<?php

namespace KitLoong\MigrationsGenerator\Repositories;

use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Repositories\Entities\MariaDB\CheckConstraint;

class MariaDBRepository extends Repository
{
    /**
     * Get a check constraint definition with `json_valid` by column.
     *
     * @param  string  $table  Table name.
     * @param  string  $column  Column name.
     * @return \KitLoong\MigrationsGenerator\Repositories\Entities\MariaDB\CheckConstraint|null
     */
    public function getCheckConstraintForJson(string $table, string $column): ?CheckConstraint
    {
        $column = DB::selectOne(
            "SELECT * FROM INFORMATION_SCHEMA.CHECK_CONSTRAINTS
            WHERE TABLE_NAME = '$table'
                AND CONSTRAINT_SCHEMA = '" . DB::getDatabaseName() . "'
                AND LEVEL = 'Column'
                AND CHECK_CLAUSE LIKE '%json_valid(`$column`)%'"
        );
        return $column === null ? null : new CheckConstraint($column);
    }
}
