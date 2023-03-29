<?php

namespace KitLoong\MigrationsGenerator\Repositories\Entities\MariaDB;

use Illuminate\Support\Collection;
use stdClass;

/**
 * Class CheckConstraint
 *
 * The CHECK constraint is used to limit the value range that can be placed in a column.
 *
 * @see https://mariadb.com/kb/en/constraint/#check-constraints
 */
class CheckConstraint
{
    /** @var string */
    private $constraintCatalog;

    /** @var string */
    private $constraintSchema;

    /** @var string */
    private $tableName;

    /** @var string */
    private $constraintName;

    /** @var string|null */
    private $level;

    /** @var string */
    private $checkClause;

    public function __construct(stdClass $column)
    {
        // Convert column property to case-insensitive
        // Issue https://github.com/kitloong/laravel-migrations-generator/issues/34
        $lowerKey = (new Collection((array) $column))->mapWithKeys(function ($item, $key) {
            return [strtolower($key) => $item];
        });

        $this->constraintCatalog = $lowerKey['constraint_catalog'];
        $this->constraintSchema  = $lowerKey['constraint_schema'];
        $this->tableName         = $lowerKey['table_name'];
        $this->constraintName    = $lowerKey['constraint_name'];
        $this->level             = null;

        if (isset($lowerKey['level'])) {
            $this->level = $lowerKey['level'];
        }

        $this->checkClause = $lowerKey['check_clause'];
    }

    /**
     * Always contains the string 'def'.
     */
    public function getConstraintCatalog(): string
    {
        return $this->constraintCatalog;
    }

    /**
     * Database name.
     */
    public function getConstraintSchema(): string
    {
        return $this->constraintSchema;
    }

    /**
     * Table name.
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * Constraint name.
     */
    public function getConstraintName(): string
    {
        return $this->constraintName;
    }

    /**
     * Type of the constraint ('Column' or 'Table'). From MariaDB 10.5.10
     *
     * @return string|null NULL if MariaDB < 10.5.10
     */
    public function getLevel(): ?string
    {
        return $this->level;
    }

    /**
     * Constraint clause.
     */
    public function getCheckClause(): string
    {
        return $this->checkClause;
    }
}
