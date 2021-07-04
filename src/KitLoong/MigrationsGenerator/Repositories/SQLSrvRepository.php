<?php

namespace KitLoong\MigrationsGenerator\Repositories;

use Illuminate\Support\Collection;
use KitLoong\MigrationsGenerator\MigrationsGeneratorSetting;

class SQLSrvRepository
{
    public function getSpatialIndexNames(string $table): Collection
    {
        $setting = app(MigrationsGeneratorSetting::class);
        $columns = $setting->getConnection()
            ->select("
                SELECT idx.name AS indexname
                FROM sys.tables AS tbl
                    JOIN sys.schemas AS scm ON tbl.schema_id = scm.schema_id
                    JOIN sys.indexes AS idx ON tbl.object_id = idx.object_id
                    JOIN sys.index_columns AS idxcol ON idx.object_id = idxcol.object_id AND idx.index_id = idxcol.index_id
                    JOIN sys.columns AS col ON idxcol.object_id = col.object_id AND idxcol.column_id = col.column_id
                WHERE " . $this->getTableWhereClause($table, 'scm.name', 'tbl.name') . "
                    AND idx.type = 4
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
     * Returns the where clause to filter schema and table name in a query.
     *
     * @param string $table        The full qualified name of the table.
     * @param string $schemaColumn The name of the column to compare the schema to in the where clause.
     * @param string $tableColumn  The name of the column to compare the table to in the where clause.
     *
     * @return string
     */
    private function getTableWhereClause(string $table, string $schemaColumn, string $tableColumn): string
    {
        if (strpos($table, '.') !== false) {
            [$schema, $table] = explode('.', $table);
            $schema           = $this->quoteStringLiteral($schema);
            $table            = $this->quoteStringLiteral($table);
        } else {
            $schema = 'SCHEMA_NAME()';
            $table  = $this->quoteStringLiteral($table);
        }

        return sprintf('(%s = %s AND %s = %s)', $tableColumn, $table, $schemaColumn, $schema);
    }

    /**
     * Quotes a literal string.
     * This method is NOT meant to fix SQL injections!
     * It is only meant to escape this platform's string literal
     * quote character inside the given literal string.
     *
     * @param string $str The literal string to be quoted.
     *
     * @return string The quoted literal string.
     */
    private function quoteStringLiteral(string $str): string
    {
        $c = $this->getStringLiteralQuoteCharacter();

        return $c . str_replace($c, $c . $c, $str) . $c;
    }

    /**
     * Gets the character used for string literal quoting.
     *
     * @return string
     */
    private function getStringLiteralQuoteCharacter(): string
    {
        return "'";
    }
}
