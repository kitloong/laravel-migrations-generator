<?php

namespace KitLoong\MigrationsGenerator\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use KitLoong\MigrationsGenerator\Repositories\Entities\ProcedureDefinition;
use KitLoong\MigrationsGenerator\Repositories\Entities\SQLSrv\ColumnDefinition;
use KitLoong\MigrationsGenerator\Repositories\Entities\SQLSrv\ViewDefinition;

class SQLSrvRepository extends Repository
{
    /**
     * Spatial index ID
     *
     * @see https://docs.microsoft.com/en-us/sql/relational-databases/system-catalog-views/sys-indexes-transact-sql?view=sql-server-ver15
     */
    public const SPATIAL_INDEX_ID = 4;

    /**
     * Get a list of spatial indexes.
     *
     * @param  string  $table  Table name.
     * @return \Illuminate\Support\Collection<string>
     */
    public function getSpatialIndexNames(string $table): Collection
    {
        $columns     = DB::select(
            "SELECT idx.name AS indexname
                FROM sys.tables AS tbl
                    JOIN sys.schemas AS scm ON tbl.schema_id = scm.schema_id
                    JOIN sys.indexes AS idx ON tbl.object_id = idx.object_id
                    JOIN sys.index_columns AS idxcol ON idx.object_id = idxcol.object_id AND idx.index_id = idxcol.index_id
                    JOIN sys.columns AS col ON idxcol.object_id = col.object_id AND idxcol.column_id = col.column_id
                WHERE " . $this->getTableWhereClause($table, 'scm.name', 'tbl.name') . "
                    AND idx.type = " . self::SPATIAL_INDEX_ID
        );
        $definitions = new Collection();

        if (count($columns) > 0) {
            foreach ($columns as $column) {
                $definitions->push($column->indexname);
            }
        }

        return $definitions;
    }

    /**
     * Get column definition by table and column name.
     *
     * @param  string  $table  Table name.
     * @param  string  $column  Column name..
     */
    public function getColumnDefinition(string $table, string $column): ?ColumnDefinition
    {
        $result = DB::selectOne(
            "SELECT col.name,
                       type.name AS type,
                       col.max_length AS length,
                       ~col.is_nullable AS notnull,
                       def.definition AS [default],
                       col.scale,
                       col.precision,
                       col.is_identity AS autoincrement,
                       col.collation_name AS collation,
                       CAST(prop.value AS NVARCHAR(MAX)) AS comment -- CAST avoids driver error for sql_variant type
                FROM sys.columns AS col
                    JOIN sys.types AS type
                        ON col.user_type_id = type.user_type_id
                    JOIN sys.objects AS obj
                        ON col.object_id = obj.object_id
                    JOIN sys.schemas AS scm
                        ON obj.schema_id = scm.schema_id
                    LEFT JOIN sys.default_constraints def
                        ON col.default_object_id = def.object_id
                            AND col.object_id = def.parent_object_id
                    LEFT JOIN sys.extended_properties AS prop
                        ON obj.object_id = prop.major_id
                            AND col.column_id = prop.minor_id
                            AND prop.name = 'MS_Description'
                WHERE obj.type = 'U'
                    AND " . $this->getTableWhereClause($table, 'scm.name', 'obj.name') . "
                    AND col.name = " . $this->quoteStringLiteral($column)
        );
        return $result === null ? null : new ColumnDefinition($result);
    }

    /**
     * Get single view name with definition.
     *
     * @param  string  $name  View name.
     */
    public function getView(string $name): ?ViewDefinition
    {
        // The definition is NULL if encrypted
        // Filter NULL value to always return view definition.
        // https://docs.microsoft.com/en-us/sql/relational-databases/system-catalog-views/sys-sql-modules-transact-sql?view=sql-server-ver15
        $view = DB::selectOne(
            "SELECT name, definition
                FROM sys.sysobjects
                    INNER JOIN sys.sql_modules ON (sys.sysobjects.id = sys.sql_modules.object_id)
                WHERE type = 'V'
                    AND object_id = object_id(
                        '$name'
                    )
                    AND definition IS NOT NULL
                ORDER BY name"
        );
        return $view === null ? null : new ViewDefinition($view->name, $view->definition);
    }

    /**
     * Returns the where clause to filter schema and table name in a query.
     *
     * @param  string  $table  The full qualified name of the table.
     * @param  string  $schemaColumn  The name of the column to compare the schema to in the where clause.
     * @param  string  $tableColumn  The name of the column to compare the table to in the where clause.
     * @see https://github.com/doctrine/dbal/blob/3.1.x/src/Platforms/SQLServer2012Platform.php#L1064
     */
    private function getTableWhereClause(string $table, string $schemaColumn, string $tableColumn): string
    {
        $schema = 'SCHEMA_NAME()';

        if (strpos($table, '.') !== false) {
            [$schema, $table] = explode('.', $table);
            $schema           = $this->quoteStringLiteral($schema);
        }

        $table = $this->quoteStringLiteral($table);

        return sprintf('(%s = %s AND %s = %s)', $tableColumn, $table, $schemaColumn, $schema);
    }

    /**
     * Get a list of stored procedures.
     *
     * @return \Illuminate\Support\Collection<\KitLoong\MigrationsGenerator\Repositories\Entities\ProcedureDefinition>
     */
    public function getProcedures(): Collection
    {
        $list       = new Collection();
        $procedures = DB::select(
            "SELECT name, definition
            FROM sys.sysobjects
                INNER JOIN sys.sql_modules ON (sys.sysobjects.id = sys.sql_modules.object_id)
            WHERE type = 'P'
                AND definition IS NOT NULL
            ORDER BY name"
        );

        foreach ($procedures as $procedure) {
            $list->push(new ProcedureDefinition($procedure->name, $procedure->definition));
        }

        return $list;
    }

    /**
     * Get enum values.
     *
     * @param  string  $table  Table name.
     * @param  string  $column  Column name.
     */
    public function getEnumPresetValues(string $table, string $column): Collection
    {
        $result = DB::selectOne(
            "SELECT con.definition
                FROM sys.check_constraints con
                JOIN sys.objects t
                    ON con.parent_object_id = t.object_id
                JOIN sys.all_columns col
                    ON con.parent_column_id = col.column_id
                    AND con.parent_object_id = col.object_id
                WHERE t.name = '$table'
                    AND col.name = '$column'
                    AND con.definition IS NOT NULL"
        );

        if ($result === null) {
            return new Collection();
        }

        $separator = "[$column]=N'";

        // eg: ([enum_default]=N'hard' OR [enum_default]=N'easy')
        $value = Str::replaceFirst('(' . $separator, '', $result->definition);
        $value = Str::substr($value, 0, -2);

        return new Collection(array_reverse(explode('\' OR ' . $separator, $value)));
    }

    /**
     * Get a list of custom data types.
     *
     * @return \Illuminate\Support\Collection<string>
     */
    public function getCustomDataTypes(): Collection
    {
        $rows  = DB::select("SELECT * FROM sys.types WHERE is_user_defined = 1");
        $types = new Collection();

        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $types->push($row->name);
            }
        }

        return $types;
    }
}
