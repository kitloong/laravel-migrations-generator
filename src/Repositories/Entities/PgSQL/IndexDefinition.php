<?php

namespace KitLoong\MigrationsGenerator\Repositories\Entities\PgSQL;

class IndexDefinition
{
    public function __construct(
        private readonly string $tableName,
        private readonly string $indexName,
        private readonly string $indexDef,
    ) {
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getIndexName(): string
    {
        return $this->indexName;
    }

    public function getIndexDef(): string
    {
        return $this->indexDef;
    }
}
