<?php

namespace KitLoong\MigrationsGenerator\Support;

use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Enum\Driver;

trait AssetNameQuote
{
    /**
     * Checks if this identifier is quoted.
     */
    public function isIdentifierQuoted(string $identifier): bool
    {
        return isset($identifier[0]) && ($identifier[0] === '`' || $identifier[0] === '"' || $identifier[0] === '[');
    }

    /**
     * Trim quotes from the identifier.
     */
    public function trimQuotes(string $identifier): string
    {
        return str_replace(['`', '"', '[', ']'], '', $identifier);
    }

    /**
     * Wrap a single string in keyword identifiers.
     */
    public function quoteIdentifier(string $value): string
    {
        switch (DB::getDriverName()) {
            case Driver::SQLSRV->value:
                return $value === '*' ? $value : '[' . str_replace(']', ']]', $value) . ']';

            case Driver::MARIADB->value:
            case Driver::MYSQL->value:
                return $value === '*' ? $value : '`' . str_replace('`', '``', $value) . '`';

            default:
                if ($value !== '*') {
                    return '"' . str_replace('"', '""', $value) . '"';
                }

                return $value;
        }
    }
}
