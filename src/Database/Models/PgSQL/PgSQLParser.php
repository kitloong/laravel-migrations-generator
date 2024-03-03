<?php

namespace KitLoong\MigrationsGenerator\Database\Models\PgSQL;

use KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType;

trait PgSQLParser
{
    /**
     * Parse default value based on column type.
     */
    public function parseDefault(?string $default, ColumnType $columnType): ?string
    {
        if ($default === null) {
            return null;
        }

        if (preg_match('/^NULL::/', $default) === 1) {
            return null;
        }

        if (preg_match("/^['(](.*)[')]::/", $default, $matches) === 1) {
            $default = $matches[1];
        }

        if ($columnType === ColumnType::STRING || $columnType === ColumnType::TEXT) {
            $default = str_replace("''", "'", $default);
        }

        return $default;
    }
}
