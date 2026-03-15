<?php

namespace KitLoong\MigrationsGenerator\Tests\Unit\Database\Models;

use KitLoong\MigrationsGenerator\Database\Models\DatabaseColumn;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType;

/**
 * Test implementation of DatabaseColumn for testing purposes
 */
class TestDatabaseColumn extends DatabaseColumn
{
    /**
     * Expose the protected escapeDefault method for testing
     */
    public function testEscapeDefault(?string $default): ?string
    {
        return $this->escapeDefault($default);
    }

    protected function getColumnType(string $type): ColumnType
    {
        return ColumnType::STRING;
    }
}
