<?php

namespace KitLoong\MigrationsGenerator\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Repositories\Entities\PgSQL\IndexDefinition;
use KitLoong\MigrationsGenerator\Repositories\Entities\ProcedureDefinition;

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

    /**
     * Get a list of custom data types.
     *
     * @source https://stackoverflow.com/questions/3660787/how-to-list-custom-types-using-postgres-information-schema
     * @return \Illuminate\Support\Collection<string>
     */
    public function getCustomDataTypes(): Collection
    {
        $searchPath = DB::connection()->getConfig('search_path') ?: DB::connection()->getConfig('schema');

        $rows  = DB::select(
            "SELECT n.nspname as schema, t.typname as type
                    FROM pg_type t
                        LEFT JOIN pg_catalog.pg_namespace n ON n.oid = t.typnamespace
                    WHERE (t.typrelid = 0 OR (SELECT c.relkind = 'c' FROM pg_catalog.pg_class c WHERE c.oid = t.typrelid))
                        AND NOT EXISTS(SELECT 1 FROM pg_catalog.pg_type el WHERE el.oid = t.typelem AND el.typarray = t.oid)
                        AND n.nspname IN ('$searchPath');"
        );
        $types = new Collection();

        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $types->push($row->type);
            }
        }

        return $types;
    }

    /**
     * Get a list of stored procedures.
     *
     * @return \Illuminate\Support\Collection<\KitLoong\MigrationsGenerator\Repositories\Entities\ProcedureDefinition>
     */
    public function getProcedures(): Collection
    {
        $list = new Collection();

        $searchPath = DB::connection()->getConfig('search_path') ?: DB::connection()->getConfig('schema');

        $procedures = DB::select(
            "SELECT proname, pg_get_functiondef(pg_proc.oid) AS definition
            FROM pg_catalog.pg_proc
                JOIN pg_namespace ON pg_catalog.pg_proc.pronamespace = pg_namespace.oid
            WHERE prokind = 'p'
                AND pg_namespace.nspname = '$searchPath'"
        );

        foreach ($procedures as $procedure) {
            $definition = str_replace('$procedure', '$', $procedure->definition);
            $list->push(new ProcedureDefinition($procedure->proname, $definition));
        }

        return $list;
    }
}
