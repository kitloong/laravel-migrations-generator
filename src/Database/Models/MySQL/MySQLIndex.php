<?php

namespace KitLoong\MigrationsGenerator\Database\Models\MySQL;

use KitLoong\MigrationsGenerator\Database\Models\DatabaseIndex;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\IndexType;

class MySQLIndex extends DatabaseIndex
{
    /**
     * @inheritDoc
     */
    public function __construct(string $table, array $index)
    {
        parent::__construct($table, $index);

        switch ($this->type) {
            case IndexType::PRIMARY:
                // Reset name to empty to indicate use the database platform naming.
                $this->name = '';
                break;

            default:
        }
    }
}
