<?php

namespace KitLoong\MigrationsGenerator\DBAL\Models\MySQL;

use KitLoong\MigrationsGenerator\DBAL\Models\DBALIndex;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\IndexType;

class MySQLIndex extends DBALIndex
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
