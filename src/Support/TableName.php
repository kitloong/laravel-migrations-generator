<?php

namespace KitLoong\MigrationsGenerator\Support;

use Illuminate\Support\Facades\DB;

trait TableName
{
    /**
     * Strips table prefix.
     *
     * @param  string  $table  Table name.
     */
    public function stripTablePrefix(string $table): string
    {
        return substr($table, strlen(DB::getTablePrefix()));
    }
}
