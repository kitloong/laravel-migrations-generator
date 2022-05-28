<?php

namespace KitLoong\MigrationsGenerator\Repositories\Entities\PgSQL;

class IndexDefinition
{
    private $tableName;
    private $indexName;
    private $indexDef;

    public function __construct(string $tableName, string $indexName, string $indexDef)
    {
        $this->tableName = $tableName;
        $this->indexName = $indexName;
        $this->indexDef  = $indexDef;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @return string
     */
    public function getIndexName(): string
    {
        return $this->indexName;
    }

    /**
     * @return string
     */
    public function getIndexDef(): string
    {
        return $this->indexDef;
    }
}
