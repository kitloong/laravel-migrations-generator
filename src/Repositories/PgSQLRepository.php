<?php

namespace KitLoong\MigrationsGenerator\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Repositories\Entities\PgSQL\IndexDefinition;

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
        $result = DB::selectOne(
            "SELECT pg_catalog.format_type(a.atttypid, a.atttypmod) as datatype
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
                    AND a.attname='$column'"
        );
        return $result === null ? null : $result->datatype;
    }

    /**
     * Get column default value by table and column name.
     *
     * @param  string  $table  Table name.
     * @param  string  $column  Column name.
     * @return string|null
     */
    public function getDefaultByColumnName(string $table, string $column): ?string
    {
        $result = DB::selectOne(
            "SELECT pg_get_expr(d.adbin, d.adrelid) AS default_value
                FROM
                    pg_catalog.pg_attribute a
                LEFT JOIN
                    pg_catalog.pg_attrdef d ON (a.attrelid, a.attnum) = (d.adrelid, d.adnum)
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
                    AND a.attname='$column'"
        );
        return $result === null ? null : $result->default_value;
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
        $result = DB::selectOne(
            "SELECT pgc.conname AS constraint_name,
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
                    AND ccu.column_name='$column'"
        );
        return $result === null ? null : $result->definition;
    }

    /**
     * Get a list of spatial indexes.
     *
     * @param  string  $table  Table name.
     * @return \Illuminate\Support\Collection<\KitLoong\MigrationsGenerator\Repositories\Entities\PgSQL\IndexDefinition>
     */
    public function getSpatialIndexes(string $table): Collection
    {
        $columns     = DB::select(
            "SELECT tablename,
                       indexname,
                       indexdef
                FROM pg_indexes
                WHERE tablename = '$table'
                    AND indexdef LIKE '% USING gist %'"
        );
        $definitions = new Collection();
        if (count($columns) > 0) {
            foreach ($columns as $column) {
                $definitions->push(
                    new IndexDefinition(
                        $column->tablename,
                        $column->indexname,
                        $column->indexdef
                    )
                );
            }
        }
        return $definitions;
    }

    /**
     * Get a list of fulltext indexes.
     *
     * @param  string  $table  Table name.
     * @return \Illuminate\Support\Collection<\KitLoong\MigrationsGenerator\Repositories\Entities\PgSQL\IndexDefinition>
     */
    public function getFulltextIndexes(string $table): Collection
    {
        $columns     = DB::select(
            "SELECT tablename,
                       indexname,
                       indexdef
                FROM pg_indexes
                WHERE tablename = '$table'
                    AND indexdef LIKE '%to_tsvector(%'"
        );
        $definitions = new Collection();
        if (count($columns) > 0) {
            foreach ($columns as $column) {
                $definitions->push(
                    new IndexDefinition(
                        $column->tablename,
                        $column->indexname,
                        $column->indexdef
                    )
                );
            }
        }
        return $definitions;
    }
}
