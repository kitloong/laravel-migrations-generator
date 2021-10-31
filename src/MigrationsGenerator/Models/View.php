<?php

namespace MigrationsGenerator\Models;

class View extends Model
{
    private $name;
    private $quotedName;
    private $createViewSql;

    public function __construct(string $name, string $quotedName, string $sql)
    {
        $this->name          = $name;
        $this->quotedName    = $quotedName;
        $this->createViewSql = $sql;
    }

    /**
     * Get view name, always unquoted.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get view name, always quoted.
     *
     * @return string
     */
    public function getQuotedName(): string
    {
        return $this->quotedName;
    }

    /**
     * Get full create view SQL.
     *
     * @return string
     */
    public function getCreateViewSql(): string
    {
        return $this->createViewSql;
    }
}
