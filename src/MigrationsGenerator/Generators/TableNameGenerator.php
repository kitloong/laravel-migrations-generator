<?php

namespace MigrationsGenerator\Generators;

use MigrationsGenerator\MigrationsGeneratorSetting;

class TableNameGenerator
{
    /**
     * Strips prefix from table name.
     *
     * @param  string  $table  Table name.
     * @return string
     */
    public function stripPrefix(string $table): string
    {
        return substr($table, strlen(app(MigrationsGeneratorSetting::class)->getConnection()->getTablePrefix()));
    }
}
