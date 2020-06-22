<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/04/07
 */

namespace KitLoong\MigrationsGenerator\Repositories;

use KitLoong\MigrationsGenerator\MigrationsGeneratorSetting;

class PgSQLRepository
{
    public function getTypeByColumnName(string $table, string $columnName): ?string
    {
        /** @var MigrationsGeneratorSetting $setting */
        $setting = app(MigrationsGeneratorSetting::class);

        $column = $setting->getConnection()
            ->select("SELECT
    pg_catalog.format_type(a.atttypid, a.atttypmod) as \"datatype\"
FROM
    pg_catalog.pg_attribute a
WHERE
    a.attnum > 0
    AND NOT a.attisdropped
    AND a.attrelid = (
        SELECT c.oid
        FROM pg_catalog.pg_class c
            LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
        WHERE c.relname ~ '^(${table})$'
            AND pg_catalog.pg_table_is_visible(c.oid)
    )
    AND a.attname='${columnName}'");
        if (count($column) > 0) {
            return $column[0]->datatype;
        }
        return null;
    }
}
