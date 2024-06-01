<?php

namespace KitLoong\MigrationsGenerator\Database\Models\SQLSrv;

use Illuminate\Support\Str;
use KitLoong\MigrationsGenerator\Database\Models\DatabaseIndex;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\IndexType;

class SQLSrvIndex extends DatabaseIndex
{
    /**
     * @inheritDoc
     */
    public function __construct(string $table, array $index)
    {
        parent::__construct($table, $index);

        switch ($this->type) {
            case IndexType::PRIMARY:
                $this->resetPrimaryNameToEmptyIfIsDefaultName();
                break;

            default:
        }
    }

    /**
     * Reset primary index name to empty if the name is using default naming convention.
     *
     * @see https://learnsql.com/cookbook/what-is-the-default-constraint-name-in-sql-server/ for default naming convention.
     */
    private function resetPrimaryNameToEmptyIfIsDefaultName(): void
    {
        $prefix = 'pk__' . Str::substr($this->tableName, 0, 8) . '__';

        // Can be improved by generate exact 16 characters of sequence number instead of `\w{16}`
        // if the rules of sequence number generation is known.
        if ($this->name !== Str::match('/' . $prefix . '\w{16}/', $this->name)) {
            return;
        }

        $this->name = '';
    }
}
