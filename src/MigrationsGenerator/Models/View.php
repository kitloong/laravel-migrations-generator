<?php

namespace MigrationsGenerator\Models;

class View extends Model
{
    private $name;
    private $quotedName;
    private $unquotedName;
    private $createViewSql;

    public function __construct(string $name, string $quotedName, string $unquotedName, string $sql)
    {
        $this->name          = $name;
        $this->quotedName    = $quotedName;
        $this->unquotedName  = $unquotedName;
        $this->createViewSql = $sql;
    }

    /**
     * Get view name, sometime is quoted.
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
     * @return string
     */
    public function getUnquotedName(): string
    {
        return $this->unquotedName;
    }

    /**
     * @return string
     */
    public function getCreateViewSql(): string
    {
        return $this->createViewSql;
    }
}
