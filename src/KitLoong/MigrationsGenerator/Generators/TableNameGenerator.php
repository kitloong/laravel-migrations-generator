<?php

namespace KitLoong\MigrationsGenerator\Generators;

use KitLoong\MigrationsGenerator\MigrationsGeneratorSetting;

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
