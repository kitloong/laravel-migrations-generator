<?php

namespace MigrationsGenerator\Repositories;

use Illuminate\Support\Collection;
use MigrationsGenerator\MigrationsGeneratorSetting;
use MigrationsGenerator\Schema\SQLSrv\Column;
use stdClass;

class SQLSrvRepository extends Repository
{
    public const INDEX_TYPE_SPATIAL = 4;
    private $setting;

    public function __construct(MigrationsGeneratorSetting $setting)
    {
        $this->setting = $setting;
    }

    /**
     * Get a list of spatial indexes.
     *
     * @param  string  $table  Table name.
     * @return \Illuminate\Support\Collection<string>
     */
    public function getSpatialIndexNames(string $table): Collection
    {
        $columns     = $this->setting->getConnection()
            ->select("
                SELECT idx.name AS indexname
                FROM sys.tables AS tbl
                    JOIN sys.schemas AS scm ON tbl.schema_id = scm.schema_id
                    JOIN sys.indexes AS idx ON tbl.object_id = idx.object_id
                    JOIN sys.index_columns AS idxcol ON idx.object_id = idxcol.object_id AND idx.index_id = idxcol.index_id
                    JOIN sys.columns AS col ON idxcol.object_id = col.object_id AND idxcol.column_id = col.column_id
                WHERE ".$this->getTableWhereClause($table, 'scm.name', 'tbl.name')."
                    AND idx.type = ".self::INDEX_TYPE_SPATIAL."
                ");
        $definitions = collect([]);
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
     * @return \MigrationsGenerator\Schema\SQLSrv\Column|null
     */
    public function getColumnDefinition(string $table, string $column): ?Column
    {
        $columns = $this->setting->getConnection()
            ->select("
                SELECT col.name,
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
                    AND ".$this->getTableWhereClause($table, 'scm.name', 'obj.name')."
                    AND col.name = ".$this->quoteStringLiteral($column)."
            ");
        if (count($columns) > 0) {
            $column = $columns[0];
            return new Column(
                $column->name,
                $column->type,
                $column->length,
                $column->notnull,
                $column->scale,
                $column->precision,
                $column->autoincrement,
                $column->default,
                $column->collation,
                $column->comment
            );
        }
        return null;
    }

    /**
     * Get single view name with definition.
     *
     * @param  string  $view  View name.
     * @return \stdClass{name: string, definition: string}|null
     */
    public function getView(string $view): ?stdClass
    {
        $dbView = $this->setting->getConnection()
            ->selectOne("
                SELECT name, definition
                FROM sys.sysobjects
                    INNER JOIN sys.sql_modules ON (sys.sysobjects.id = sys.sql_modules.object_id)
                WHERE type = 'V'
                    AND object_id = object_id(
                        '$view'
                    )
                ORDER BY name
            ");
        if ($dbView !== null) {
            return $dbView;
        }
        return null;
    }

    /**
     * Returns the where clause to filter schema and table name in a query.
     *
     * @param  string  $table  The full qualified name of the table.
     * @param  string  $schemaColumn  The name of the column to compare the schema to in the where clause.
     * @param  string  $tableColumn  The name of the column to compare the table to in the where clause.
     *
     * @return string
     * @see https://github.com/doctrine/dbal/blob/3.1.x/src/Platforms/SQLServer2012Platform.php#L1064
     */
    private function getTableWhereClause(string $table, string $schemaColumn, string $tableColumn): string
    {
        if (strpos($table, '.') !== false) {
            [$schema, $table] = explode('.', $table);
            $schema = $this->quoteStringLiteral($schema);
        } else {
            $schema = 'SCHEMA_NAME()';
        }
        $table = $this->quoteStringLiteral($table);

        return sprintf('(%s = %s AND %s = %s)', $tableColumn, $table, $schemaColumn, $schema);
    }
}
