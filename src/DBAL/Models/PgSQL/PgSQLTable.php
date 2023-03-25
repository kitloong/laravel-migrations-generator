<?php

namespace KitLoong\MigrationsGenerator\DBAL\Models\PgSQL;

use Doctrine\DBAL\Schema\Column as DoctrineDBALColumn;
use Doctrine\DBAL\Schema\Index as DoctrineDBALIndex;
use KitLoong\MigrationsGenerator\DBAL\Models\DBALTable;
use KitLoong\MigrationsGenerator\Repositories\Entities\PgSQL\IndexDefinition;
use KitLoong\MigrationsGenerator\Repositories\PgSQLRepository;
use KitLoong\MigrationsGenerator\Schema\Models\Column;
use KitLoong\MigrationsGenerator\Schema\Models\CustomColumn;
use KitLoong\MigrationsGenerator\Schema\Models\Index;

class PgSQLTable extends DBALTable
{
    /** @var \KitLoong\MigrationsGenerator\Repositories\PgSQLRepository */
    private $repository;

    /**
     * @inheritDoc
     */
    protected function handle(): void
    {
        $this->repository = app(PgSQLRepository::class);

        $this->pushFulltextIndexes();

        $this->indexes = $this->indexes->sortBy(function (Index $index) {
            return $index->getName();
        })->values();
    }

    /**
     * @inheritDoc
     */
    protected function makeColumn(string $table, DoctrineDBALColumn $column): Column
    {
        return new PgSQLColumn($table, $column);
    }

    /**
     * @inheritDoc
     * @throws \Doctrine\DBAL\Exception
     */
    protected function makeCustomColumn(string $table, DoctrineDBALColumn $column): CustomColumn
    {
        return new PgSQLCustomColumn($table, $column);
    }

    /**
     * @inheritDoc
     */
    protected function makeIndex(string $table, DoctrineDBALIndex $index): Index
    {
        return new PgSQLIndex($table, $index);
    }

    private function pushFulltextIndexes(): void
    {
        // Get fulltext indexes.
        $fulltextIndexes = $this->repository->getFulltextIndexes($this->name);
        $fulltextIndexes->each(function (IndexDefinition $indexDefinition): void {
            // Get column names in array
            // eg: CREATE INDEX fulltext_custom ON public.test_index_pgsql USING gin (to_tsvector('english'::regconfig, (fulltext_custom)::text))
            //     Get "fulltext_custom"
            preg_match_all('/to_tsvector\((.*), \((.*)\)::text/U', $indexDefinition->getIndexDef(), $matches);

            if (empty($matches[2])) {
                return;
            }

            $columns = $matches[2];

            $this->indexes->push(
                new PgSQLIndex(
                    $this->name,
                    new DoctrineDBALIndex(
                        $indexDefinition->getIndexName(),
                        $columns,
                        false,
                        false,
                        ['fulltext'],
                        []
                    )
                )
            );
        });
    }
}
