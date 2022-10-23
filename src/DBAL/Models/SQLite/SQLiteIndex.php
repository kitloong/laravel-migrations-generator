<?php

namespace KitLoong\MigrationsGenerator\DBAL\Models\SQLite;

use KitLoong\MigrationsGenerator\DBAL\Models\DBALIndex;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\IndexType;

class SQLiteIndex extends DBALIndex
{
    protected function handle(): void
    {
        switch ($this->type) {
            case IndexType::PRIMARY():
                // Reset name to empty to indicate use the database platform naming.
                $this->name = '';
                break;

            default:
        }
    }
}
