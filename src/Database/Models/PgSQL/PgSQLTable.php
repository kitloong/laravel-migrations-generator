<?php

namespace KitLoong\MigrationsGenerator\Database\Models\PgSQL;

use Illuminate\Support\Collection;
use KitLoong\MigrationsGenerator\Database\Models\DatabaseTable;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\IndexType;
use KitLoong\MigrationsGenerator\Repositories\Entities\PgSQL\IndexDefinition;
use KitLoong\MigrationsGenerator\Repositories\PgSQLRepository;
use KitLoong\MigrationsGenerator\Schema\Models\Column;
use KitLoong\MigrationsGenerator\Schema\Models\Index;
use KitLoong\MigrationsGenerator\Schema\Models\UDTColumn;

class PgSQLTable extends DatabaseTable
{
    private PgSQLRepository $repository;

    /**
     * @inheritDoc
     */
    public function __construct(array $table, Collection $columns, Collection $indexes, Collection $userDefinedTypes)
    {
        parent::__construct($table, $columns, $indexes, $userDefinedTypes);

        $this->repository = app(PgSQLRepository::class);

        $this->updateFulltextIndexes();

        $this->indexes = $this->indexes->sortBy(static fn (Index $index) => $index->getName())->values();
    }

    /**
     * @inheritDoc
     */
    protected function makeColumn(string $table, array $column): Column
    {
        return new PgSQLColumn($table, $column);
    }

    /**
     * @inheritDoc
     */
    protected function makeUDTColumn(string $table, array $column): UDTColumn
    {
        return new PgSQLUDTColumn($table, $column);
    }

    /**
     * @inheritDoc
     */
    protected function makeIndex(string $table, array $index, bool $hasUDTColumn): Index
    {
        return new PgSQLIndex($table, $index, $hasUDTColumn);
    }

    /**
     * The fulltext index column is empty by default.
     * This method query the DB to get the fulltext index columns and update the indexes collection.
     */
    private function updateFulltextIndexes(): void
    {
        $tableName = $this->name;

        // Get fulltext indexes.
        $fulltextIndexes = $this->repository->getFulltextIndexes($tableName)
            ->keyBy(static fn (IndexDefinition $indexDefinition) => $indexDefinition->getIndexName());

        $this->indexes = $this->indexes->map(static function (Index $index) use ($fulltextIndexes, $tableName) {
            if (!($index->getType() === IndexType::FULLTEXT)) {
                return $index;
            }

            if (!$fulltextIndexes->has($index->getName())) {
                return $index;
            }

            preg_match_all('/to_tsvector\((.*), \((.*)\)::text/U', $fulltextIndexes->get($index->getName())?->getIndexDef() ?? '', $matches);

            $columns = $matches[2];

            return new PgSQLIndex(
                $tableName,
                [
                    'name'    => $index->getName(),
                    'columns' => $columns,
                    'type'    => 'gin',
                    'unique'  => false,
                    'primary' => false,
                ],
                false,
            );
        });
    }
}
