<?php

namespace KitLoong\MigrationsGenerator\Repositories;

use Illuminate\Support\Facades\DB;

class SQLiteRepository extends Repository
{
    /**
     * Get table sql by table name.
     *
     * @param  string  $table  Table name.
     * @return string  The create table SQL statement.
     */
    public function getSql(string $table): string
    {
        $sql = DB::selectOne('SELECT sql FROM sqlite_master WHERE tbl_name="' . $table . '"');
        // null if the condition not met.
        return $sql === null ? '' : $sql->sql;
    }
}
