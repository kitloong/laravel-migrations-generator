<?php

namespace MigrationsGenerator\Generators;

use MigrationsGenerator\MigrationsGeneratorSetting;

class TableNameGenerator
{
    /**
     * @param  string  $table
     * @return string
     */
    public function stripPrefix(string $table): string
    {
        return substr($table, strlen(app(MigrationsGeneratorSetting::class)->getConnection()->getTablePrefix()));
    }
}
