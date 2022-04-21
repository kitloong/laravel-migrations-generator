<?php

namespace KitLoong\MigrationsGenerator\Support;

use Illuminate\Support\Facades\DB;

trait TableName
{
    /**
     * Strips prefix from table name.
     *
     * @param  string  $table  Table name.
     * @return string
     */
    public function stripPrefix(string $table): string
    {
        return substr($table, strlen(DB::getTablePrefix()));
    }
}
