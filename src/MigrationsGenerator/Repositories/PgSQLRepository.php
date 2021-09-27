<?php

namespace MigrationsGenerator\Repositories;

use Illuminate\Support\Collection;
use MigrationsGenerator\MigrationsGeneratorSetting;

class PgSQLRepository extends Repository
{
    /**
     * Get column type by table and column name.
     *
     * @param  string  $table  Table name.
     * @param  string  $column  Column name.
     * @return string|null
     */
    public function getTypeByColumnName(string $table, string $column): ?string
    {
        $setting = app(MigrationsGeneratorSetting::class);

        $columnDetail = $setting->getConnection()
            ->select("
                SELECT pg_catalog.format_type(a.atttypid, a.atttypmod) as datatype
                FROM
                    pg_catalog.pg_attribute a
                WHERE
                    a.attnum > 0
                    AND NOT a.attisdropped
                    AND a.attrelid = (
                        SELECT c.oid
                        FROM pg_catalog.pg_class c
                            LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
                        WHERE c.relname ~ '^($table)$'
                            AND pg_catalog.pg_table_is_visible(c.oid)
                    )
                    AND a.attname='$column'");
        if (count($columnDetail) > 0) {
            return $columnDetail[0]->datatype;
        }
        return null;
    }

    /**
     * Get constraint by table and column name.
     *
     * @param  string  $table  Table name.
     * @param  string  $column  Column name.
     * @return string|null
     */
    public function getCheckConstraintDefinition(string $table, string $column): ?string
    {
        $setting      = app(MigrationsGeneratorSetting::class);
        $columnDetail = $setting->getConnection()
            ->select("
                SELECT pgc.conname AS constraint_name,
                       pgc.contype,
                       ccu.table_schema AS table_schema,
                       ccu.table_name,
                       ccu.column_name,
                       pg_get_constraintdef(pgc.oid) AS definition
                FROM pg_constraint pgc
                JOIN pg_namespace nsp ON nsp.oid = pgc.connamespace
                JOIN pg_class  cls ON pgc.conrelid = cls.oid
                LEFT JOIN information_schema.constraint_column_usage ccu
                          ON pgc.conname = ccu.constraint_name
                          AND nsp.nspname = ccu.constraint_schema
                WHERE contype ='c'
                    AND ccu.table_name='$table'
                    AND ccu.column_name='$column';
            ");
        if (count($columnDetail) > 0) {
            return $columnDetail[0]->definition;
        }
        return null;
    }

    /**
     * Get a list of spatial indexes.
     *
     * @param  string  $table  Table name.
     * @return \Illuminate\Support\Collection<string>
     */
    public function getSpatialIndexNames(string $table): Collection
    {
        $setting     = app(MigrationsGeneratorSetting::class);
        $columns     = $setting->getConnection()
            ->select("
                SELECT tablename,
                       indexname,
                       indexdef
                FROM pg_indexes
                WHERE tablename = '$table'
                    AND indexdef LIKE '% USING gist %'");
        $definitions = collect([]);
        if (count($columns) > 0) {
            foreach ($columns as $column) {
                $definitions->push($column->indexname);
            }
        }
        return $definitions;
    }
}
