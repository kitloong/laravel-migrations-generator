<?php

namespace KitLoong\MigrationsGenerator\DBAL;

use Doctrine\DBAL\Schema\Table;
use Illuminate\Support\Collection;
use KitLoong\MigrationsGenerator\Schema\Schema;
use KitLoong\MigrationsGenerator\Support\AssetNameQuote;

/**
 * @template T of \Doctrine\DBAL\Platforms\AbstractPlatform
 */
abstract class DBALSchema implements Schema
{
    use AssetNameQuote;

    /**
     * @var \Doctrine\DBAL\Schema\AbstractSchemaManager<T>
     */
    protected $dbalSchema;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function __construct(RegisterColumnType $registerColumnType)
    {
        // @phpstan-ignore-next-line
        $this->dbalSchema = app(Connection::class)->getDoctrineSchemaManager();
        $registerColumnType->handle();
    }

    /**
     * @inheritDoc
     * @throws \Doctrine\DBAL\Exception
     */
    public function getTableNames(): Collection
    {
        return (new Collection($this->dbalSchema->listTableNames()))
            ->map(function ($table) {
                // The table name may contain quotes.
                // Always trim quotes before set into list.
                if ($this->isIdentifierQuoted($table)) {
                    return $this->trimQuotes($table);
                }

                return $table;
            });
    }

    /**
     * Introspects the table with the given name.
     * `listTableDetails` is deprecated since `doctrine/dbal` v3.5 and will be removed from v4.
     * This method will try to call `introspectTable` and fallback to `listTableDetails`.
     *
     * @throws \Doctrine\DBAL\Exception
     */
    protected function introspectTable(string $name): Table
    {
        if (method_exists($this->dbalSchema, 'introspectTable')) {
            return $this->dbalSchema->introspectTable($name);
        }

        // @phpstan-ignore-next-line
        return $this->dbalSchema->listTableDetails($name);
    }
}
